<?php

namespace LastCall\Crawler\Session;

use GuzzleHttp\ClientInterface;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface SessionInterface
{

    /**
     * @param string $startUrl
     *
     * @return string
     */
    public function getStartUrl($startUrl = null);

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return void
     */
    public function addRequest(RequestInterface $request);

    /**
     * @return RequestQueueInterface
     */
    public function getQueue();

    /**
     * @return ClientInterface
     */
    public function getClient();

    /**
     * @return bool
     */
    public function isFinished();

    public function onSetup();

    public function onTeardown();

    public function onRequestSending(RequestInterface $request);

    public function onRequestSuccess(
        RequestInterface $request,
        ResponseInterface $response
    );

    public function onRequestFailure(
        RequestInterface $request,
        ResponseInterface $response
    );

    public function onRequestException(
        RequestInterface $request,
        \Exception $exception,
        ResponseInterface $response = null
    );
}