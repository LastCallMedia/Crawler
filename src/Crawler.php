<?php

namespace LastCall\Crawler;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Promise\EachPromise;
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

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * Crawler constructor.
     *
     * @param \LastCall\Crawler\Session\SessionInterface $session
     * @param \GuzzleHttp\ClientInterface                $client
     */
    public function __construct(
        SessionInterface $session,
        ClientInterface $client
    ) {
        $this->session = $session;
        $this->client = $client;
    }

    /**
     * Start crawling.
     *
     * @param int    $chunk
     * @param string $baseUrl
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function start($chunk = 5, $baseUrl = null)
    {
        $this->session->init($baseUrl);
        // We need to use a double loop of generators here, because
        // if $chunk is greater than the number of items in the queue,
        // the requestWorkerFn exits the generator loop before any new
        // requests can be added by processing and cannot be restarted.

        // The outer generator ($gen) restarts the processing in that case.
        $gen = function () use ($chunk) {
            while (!$this->session->isFinished()) {
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
        while ($request = $this->session->next()) {
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
            $this->session->complete($request);

            try {
                $this->session->onRequestSuccess($request, $response);
            } catch (\Exception $e) {
                $this->session->onRequestException($request, $e, $response);
                throw $e;
            }

            return $response;
        };
    }

    private function getRequestRejectedFn(RequestInterface $request)
    {
        return function ($reason) use ($request) {
            $this->session->complete($request);
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
