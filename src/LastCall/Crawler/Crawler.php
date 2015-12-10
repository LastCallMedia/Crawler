<?php

namespace LastCall\Crawler;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Promise\PromiseIterator;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Queue\Queue;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This file contains the crawler, which is the engine that powers the rest of
 * the system.  It is responsible for queuing requests, sending them to the
 * server, receiving the responses, and dispatching the success or failure
 * events back to the session for processing.
 *
 * Most of what happens in here is implemented inside of promises, meaning we
 * can run multiple requests concurrently.  Using the PromiseIterator, we're
 * able to keep refilling the queue as we work through it, meaning we can start
 * with a single item and spider out from there.
 *
 * The basic cycle of processing goes:
 *
 * Work on the request queue until it is empty.
 * Work on the process queue until it is empty.
 * Complete.
 */
class Crawler
{

    const SENDING = 'crawler.sending';
    const SUCCESS = 'crawler.success';
    const FAIL = 'crawler.failure';
    const EXCEPTION = 'crawler.exception';
    const SETUP = 'crawler.reset';
    const TEARDOWN = 'crawler.teardown';


    /**
     * @var \LastCall\Crawler\Configuration\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    protected $queue;

    /**
     * @param array|\LastCall\Crawler\Configuration\ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config, EventDispatcherInterface $dispatcher = NULL)
    {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->configuration = $config;
        $this->queue = new Queue($config->getQueueDriver(), 'request');
        $this->attachListeners();
    }

    /**
     * @param $uri
     *
     * @return \LastCall\Crawler\Url\URLHandler
     */
    public function getUrlHandler($uri)
    {
        return $this->configuration->getUrlHandler()->forUrl($uri);
    }

    public function addRequest(RequestInterface $request)
    {
        $this->queue->push($request, $request->getMethod() . $request->getUri());
    }

    public function start($chunk = 5, $baseUrl = null)
    {
        $baseUrl = $baseUrl ?: $this->configuration->getBaseUrl();
        $this->addRequest(new Request('GET', $baseUrl));

        // We need to use a double loop of generators here, because
        // if $chunk is greater than the number of items in the queue,
        // the requestWorkerFn exits the generator loop before any new
        // requests can be added by processing and cannot be restarted.

        // The outer generator ($gen) restarts the processing in that case.
        $gen = function() use ($chunk) {
            while($this->queue->count()) {
                $inner = new EachPromise($this->getRequestWorkerFn(), [
                  'concurrency' => $chunk
                ]);
                yield $inner->promise();
            }
        };

        $outer = new EachPromise($gen(), ['concurrency' => 1]);
        return $outer->promise();
    }

    private function attachListeners() {
        foreach($this->configuration->getListeners() as $eventName => $listeners) {
            foreach($listeners as $listener) {
                $this->dispatcher->addListener($eventName, $listener);
            }
        }
        foreach($this->configuration->getSubscribers() as $subscriber) {
            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    private function getRequestWorkerFn()
    {
        while($job = $this->queue->pop()) {
            $request = $job->getData();
            try {
                $event = new CrawlerEvent($request, $this->queue, $this->getUrlHandler($request->getUri()));

                $this->dispatcher->dispatch(self::SENDING, $event);
                $promise = $this->configuration->getClient()->sendAsync($job->getData())
                  ->then($this->getRequestFulfilledFn($request, $job), $this->getRequestRejectedFn($request, $job));
                yield $promise;
            }
            catch(\Exception $e) {
                $event = new CrawlerExceptionEvent($request, NULL, $e, $this->queue, $this->getUrlHandler($request->getUri()));
                $this->dispatcher->dispatch(self::EXCEPTION, $event);
                yield \GuzzleHttp\Promise\rejection_for($e);
            }
        }
    }

    public function getRequestFulfilledFn(RequestInterface $request, Job $job)
    {
        return function (ResponseInterface $response) use ($request, $job) {
            $this->queue->complete($job);

            try {
                $event = new CrawlerResponseEvent($request, $response, $this->queue, $this->getUrlHandler($request->getUri()));
                $this->dispatcher->dispatch(self::SUCCESS, $event);
            } catch (\Exception $e) {
                $event = new CrawlerExceptionEvent($request, $response, $e, $this->queue, $this->getUrlHandler($request->getUri()));
                $this->dispatcher->dispatch(self::EXCEPTION, $event);
                throw $e;
            }
            return $response;
        };
    }

    public function getRequestRejectedFn(RequestInterface $request, Job $job)
    {
        return function ($reason) use ($request, $job) {
            $this->queue->complete($job);
            // Delegate processing of the item out to the session.
            if ($reason instanceof BadResponseException) {
                $response = $reason->getResponse();

                try {
                    $event = new CrawlerResponseEvent($request, $response, $this->queue, $this->getUrlHandler($request->getUri()));
                    $this->dispatcher->dispatch(self::FAIL, $event);
                } catch (\Exception $e) {
                    $event =  new CrawlerExceptionEvent($request, $response, $e, $this->queue, $this->getUrlHandler($request->getUri()));
                    $this->dispatcher->dispatch(self::EXCEPTION, $event);
                    throw $e;
                }

                throw $reason;
            }

            return \GuzzleHttp\Promise\rejection_for($reason);
        };
    }

    public function setUp() {
        $this->dispatcher->dispatch(self::SETUP);
    }

    public function teardown() {
        $this->dispatcher->dispatch(self::TEARDOWN);
    }
}