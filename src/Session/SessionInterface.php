<?php

namespace LastCall\Crawler\Session;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Contains data about the current crawler session and dispatches
 * events out to the subscribers.
 */
interface SessionInterface
{
    /**
     * Prepare the session for crawling.
     *
     * @param string $startUrl
     *
     * @return mixed
     */
    public function init($startUrl = '');

    /**
     * Retrieve the next request.
     *
     * @return RequestInterface|null
     */
    public function next();

    public function complete(RequestInterface $request);

    public function release(RequestInterface $request);

    /**
     * Add a request to the current session.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function addRequest(RequestInterface $request);

    /**
     * Check whether the session has completed.
     *
     * @return bool
     */
    public function isFinished();

    /**
     * Dispatch a setup event.
     */
    public function setup();

    /**
     * Dispatch a teardown event.
     */
    public function teardown();

    /**
     * Dispatch a request sending event.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function onRequestSending(RequestInterface $request);

    /**
     * Dispatch a request success event.
     *
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function onRequestSuccess(
        RequestInterface $request,
        ResponseInterface $response
    );

    /**
     * Dispatch a request failure event.
     *
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function onRequestFailure(
        RequestInterface $request,
        ResponseInterface $response
    );

    /**
     * Dispatch a request exception event.
     *
     * @param \Psr\Http\Message\RequestInterface       $request
     * @param \Exception                               $exception
     * @param \Psr\Http\Message\ResponseInterface|null $response
     *
     * @return vod
     */
    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = null
    );
}
