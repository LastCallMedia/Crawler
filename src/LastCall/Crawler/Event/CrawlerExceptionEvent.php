<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Crawler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CrawlerExceptionEvent extends CrawlerEvent
{

    private $exception;

    private $response;

    public function __construct(
      Crawler $crawler,
      RequestInterface $request,
      \Exception $exception,
      ResponseInterface $response = null
    ) {
        parent::__construct($crawler, $request);
        $this->response = $response;
        $this->exception = $exception;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getException()
    {
        return $this->exception;
    }
}