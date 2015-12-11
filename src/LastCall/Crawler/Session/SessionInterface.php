<?php

namespace LastCall\Crawler\Session;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use LastCall\Crawler\Queue\RequestQueueInterface;
use GuzzleHttp\ClientInterface;

interface SessionInterface {

    /**
     * @param string $startUrl
     *
     * @return string
     */
    public function getStartUrl($startUrl = NULL);

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
    public function onRequestSuccess(RequestInterface $request, ResponseInterface $response);
    public function onRequestFailure(RequestInterface $request, ResponseInterface $response);
    public function onRequestException(RequestInterface $request, \Exception $exception, ResponseInterface $response = NULL);
}