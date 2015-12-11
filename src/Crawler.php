<?php

namespace LastCall\Crawler;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Session\SessionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @var \LastCall\Crawler\Session\SessionInterface
     */
    protected $session;

    protected $queue;

    protected $client;

    /**
     * Crawler constructor.
     *
     * @param \LastCall\Crawler\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->queue = $session->getQueue();
        $this->client = $session->getClient();
    }

    public function addRequest(RequestInterface $request)
    {
        $this->queue->push($request);
    }

    public function start($chunk = 5, $baseUrl = null)
    {
        $start = $this->session->getStartUrl($baseUrl);
        $this->addRequest(new Request('GET', $start));

        // We need to use a double loop of generators here, because
        // if $chunk is greater than the number of items in the queue,
        // the requestWorkerFn exits the generator loop before any new
        // requests can be added by processing and cannot be restarted.

        // The outer generator ($gen) restarts the processing in that case.
        $gen = function () use ($chunk) {
            while (!$this->session->isFinished()) {
                $inner = new EachPromise($this->getRequestWorkerFn(), [
                    'concurrency' => $chunk
                ]);
                yield $inner->promise();
            }
        };

        $outer = new EachPromise($gen(), ['concurrency' => 1]);

        return $outer->promise();
    }

    private function getRequestWorkerFn()
    {
        while ($job = $this->queue->pop()) {
            $request = $job->getData();
            try {
                $this->session->onRequestSending($request);
                $promise = $this->client->sendAsync($job->getData())
                    ->then($this->getRequestFulfilledFn($request, $job),
                        $this->getRequestRejectedFn($request, $job));
                yield $promise;
            } catch (\Exception $e) {
                $this->session->onRequestException($request, $e, null);
                yield \GuzzleHttp\Promise\rejection_for($e);
            }
        }
    }

    public function getRequestFulfilledFn(RequestInterface $request, Job $job)
    {
        return function (ResponseInterface $response) use ($request, $job) {
            $this->queue->complete($job);

            try {
                $this->session->onRequestSuccess($request, $response);
            } catch (\Exception $e) {
                $this->session->onRequestException($request, $e, $response);
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
                    $this->session->onRequestFailure($request, $response);
                } catch (\Exception $e) {
                    $this->session->onRequestException($request, $e, $response);
                    throw $e;
                }

                throw $reason;
            }

            return \GuzzleHttp\Promise\rejection_for($reason);
        };
    }

    public function setUp()
    {
        $this->session->onSetup();
    }

    public function teardown()
    {
        $this->session->onTeardown();
    }
}