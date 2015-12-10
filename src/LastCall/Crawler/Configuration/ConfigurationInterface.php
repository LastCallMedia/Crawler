<?php

namespace LastCall\Crawler\Configuration;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ConfigurationInterface
{

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient();

    /**
     * @return \LastCall\Crawler\Url\UrlHandler
     */
    public function getUrlHandler();

    /**
     * @return \LastCall\Crawler\Queue\Driver\DriverInterface
     */
    public function getQueue();

    /**
     * @return string
     */
    public function getBaseUrl();

    public function onSetup();

    public function onTeardown();

    public function onRequestSending(RequestInterface $request);

    public function onRequestSuccess(RequestInterface $request, ResponseInterface $response);

    public function onRequestFailure(RequestInterface $request, ResponseInterface $response);

    public function onRequestException(RequestInterface $request,
        ResponseInterface $response = NULL,
        \Exception $exception = NULL
    );
}