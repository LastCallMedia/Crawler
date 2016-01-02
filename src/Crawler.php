<?php

namespace LastCall\Crawler;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Session\SessionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Works through a request queue, dispatching requests to the client,
 * and responses to the session.
 */
class Crawler
{
    /**
     * @var \LastCall\Crawler\Session\SessionInterface
     */
    private $session;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    private $queue;

    /**
     * Crawler constructor.
     *
     * @param \LastCall\Crawler\Session\SessionInterface $session
     * @param \GuzzleHttp\ClientInterface                $client
     */
    public function __construct(
        SessionInterface $session,
        ClientInterface $client,
        RequestQueueInterface $queue
    ) {
        $this->session = $session;
        $this->client = $client;
        $this->queue = $queue;
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
        $this->session->start();
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

        return $outer->promise();
    }

    private function getRequestWorkerFn()
    {
        while ($request = $this->queue->pop()) {
            try {
                $this->session->onRequestSending($request);
                $promise = $this->client->sendAsync($request)
                    ->then($this->getRequestFulfilledFn($request),
                        $this->getRequestRejectedFn($request));
                yield $promise;
            } catch (\Exception $e) {
                $this->session->onRequestException($request, $e, null);
                yield \GuzzleHttp\Promise\rejection_for($e);
            }
        }
    }

    private function getRequestFulfilledFn(RequestInterface $request)
    {
        return function (ResponseInterface $response) use ($request) {
            $this->queue->complete($request);

            try {
                $requests = $this->session->onRequestSuccess($request, $response);
                $this->enqueue($requests);
            } catch (\Exception $e) {
                $requests = $this->session->onRequestException($request, $e, $response);
                $this->enqueue($requests);
                throw $e;
            }

            return $response;
        };
    }

    private function getRequestRejectedFn(RequestInterface $request)
    {
        return function ($reason) use ($request) {
            $this->queue->complete($request);
            // Delegate processing of the item out to the session.
            if ($reason instanceof BadResponseException) {
                $response = $reason->getResponse();

                try {
                    $requests = $this->session->onRequestFailure($request, $response);
                    $this->enqueue($requests);
                } catch (\Exception $e) {
                    $requests = $this->session->onRequestException($request, $e, $response);
                    $this->enqueue($requests);
                    throw $e;
                }
            }

            return \GuzzleHttp\Promise\rejection_for($reason);
        };
    }

    private function enqueue($requests)
    {
        if (is_array($requests) && !empty($requests)) {
            $this->queue->pushMultiple($requests);
        }
    }
}
