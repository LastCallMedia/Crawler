<?php

namespace LastCall\Crawler;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use LastCall\Crawler\Event\CrawlerFinishEvent;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\RequestData\RequestDataStore;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Works through a request queue, sending requests through a client, and
 * dispatching data through the dispatcher.
 */
final class Crawler
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    private $queue;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \LastCall\Crawler\RequestData\RequestDataStore
     */
    private $dataStore;

    /**
     * Crawler constructor.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \GuzzleHttp\ClientInterface                                 $client
     * @param \LastCall\Crawler\Queue\RequestQueueInterface               $queue
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ClientInterface $client,
        RequestQueueInterface $queue,
        RequestDataStore $dataStore
    ) {
        $this->dispatcher = $dispatcher;
        $this->client = $client;
        $this->queue = $queue;
        $this->dataStore = $dataStore;
    }

    /**
     * Start crawling.
     *
     * @param int $chunk
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function start($chunk = 5)
    {
        $this->dispatchStart();
        // We need to use a double loop of generators here, because
        // if $chunk is greater than the number of items in the queue,
        // the requestWorkerFn exits the generator loop before any new
        // requests can be added by processing and cannot be restarted.

        // The outer generator ($gen) restarts the processing in that case.
        $gen = function () use ($chunk) {
            while ($this->queue->count() > 0) {
                $inner = new EachPromise($this->getRequestWorkerFn(), [
                    'concurrency' => $chunk,
                ]);
                yield $inner->promise();
            }
        };

        $outer = new EachPromise($gen(), ['concurrency' => 1]);

        $finish = function ($results) {
            $this->dispatchFinish();

            return $results;
        };

        return $outer->promise()->then($finish, $finish);
    }

    /**
     * Execute all registered setup tasks.
     */
    public function setup()
    {
        $this->dispatchSetup();
    }

    /**
     * Execute all registered teardown tasks.
     */
    public function teardown()
    {
        $this->dispatchTeardown();
    }

    private function getRequestWorkerFn()
    {
        while ($request = $this->queue->pop()) {
            try {
                $this->dispatchSending($request);
                $promise = $this->client->sendAsync($request)
                    ->then($this->getRequestFulfilledFn($request),
                        $this->getRequestRejectedFn($request));
                yield $promise;
            } catch (\Exception $e) {
                // Mark the request as complete so we don't get stuck on it.
                $this->queue->complete($request);
                $this->dispatchException($request, $e, null);
                yield \GuzzleHttp\Promise\rejection_for($e);
            }
        }
    }

    private function getRequestFulfilledFn(RequestInterface $request)
    {
        return function (ResponseInterface $response) use ($request) {
            $this->queue->complete($request);

            try {
                $this->dispatchSuccess($request, $response);
            } catch (\Exception $e) {
                $this->dispatchException($request, $e, $response);
                throw $e;
            }

            return $response;
        };
    }

    private function getRequestRejectedFn(RequestInterface $request)
    {
        return function ($reason) use ($request) {
            $this->queue->complete($request);
            // Delegate processing of the item out to the dispatcher.
            if ($reason instanceof BadResponseException) {
                $response = $reason->getResponse();

                try {
                    $this->dispatchFailure($request, $response);
                } catch (\Exception $e) {
                    $this->dispatchException($request, $e, $response);
                    throw $e;
                }
            }

            return \GuzzleHttp\Promise\rejection_for($reason);
        };
    }

    private function dispatchSetup()
    {
        $this->dispatcher->dispatch(CrawlerEvents::SETUP);
    }

    private function dispatchTeardown()
    {
        $this->dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }

    private function dispatchStart()
    {
        $event = new CrawlerStartEvent();
        $this->dispatcher->dispatch(CrawlerEvents::START, $event);
        $this->enqueue($event->getAdditionalRequests());
    }

    private function dispatchFinish()
    {
        $event = new CrawlerFinishEvent($this->dataStore);
        $this->dispatcher->dispatch(CrawlerEvents::FINISH, $event);
    }

    private function dispatchSending(RequestInterface $request)
    {
        $event = new CrawlerRequestEvent($request);
        $this->dispatcher->dispatch(CrawlerEvents::SENDING, $event);
        $this->enqueue($event->getAdditionalRequests());
        $this->dataStore->merge((string) $request->getUri(), $event->getData());
    }

    private function dispatchSuccess(RequestInterface $request, ResponseInterface $response)
    {
        $event = new CrawlerResponseEvent($request, $response);
        $this->dispatcher->dispatch(CrawlerEvents::SUCCESS, $event);
        $this->enqueue($event->getAdditionalRequests());
        $this->dataStore->merge((string) $request->getUri(), $event->getData());
    }

    private function dispatchFailure(RequestInterface $request, ResponseInterface $response)
    {
        $event = new CrawlerResponseEvent($request, $response);
        $this->dispatcher->dispatch(CrawlerEvents::FAILURE, $event);
        $this->enqueue($event->getAdditionalRequests());
        $this->dataStore->merge((string) $request->getUri(), $event->getData());
    }

    private function dispatchException(RequestInterface $request, \Exception $e, ResponseInterface $response = null)
    {
        $event = new CrawlerExceptionEvent($request, $response, $e);
        $this->dispatcher->dispatch(CrawlerEvents::EXCEPTION, $event);
        $this->enqueue($event->getAdditionalRequests());
    }

    private function enqueue(array $requests)
    {
        if (!empty($requests)) {
            $this->queue->pushMultiple($requests);
        }
    }
}
