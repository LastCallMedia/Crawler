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
 * Dispatches requests to the client, and responses to the session.
 */
class Crawler
{

    /**
     * @var \LastCall\Crawler\Session\SessionInterface
     */
    private $session;

    private $queue;

    private $client;

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

    private function addRequest(RequestInterface $request)
    {
        $this->queue->push($request);
    }

    /**
     * Start crawling.
     *
     * @param int  $chunk
     * @param null $baseUrl
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
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

    private function getRequestFulfilledFn(RequestInterface $request, Job $job)
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

    private function getRequestRejectedFn(RequestInterface $request, Job $job)
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

    /**
     * Execute setup tasks.
     */
    public function setUp()
    {
        $this->session->onSetup();
    }

    /**
     * Execute teardown tasks.
     */
    public function teardown()
    {
        $this->session->onTeardown();
    }
}