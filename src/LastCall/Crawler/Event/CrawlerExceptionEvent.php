<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Crawler;
use LastCall\Crawler\Queue\QueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CrawlerExceptionEvent extends CrawlerEvent
{

    private $exception;

    private $response;

    public function __construct(
      RequestInterface $request,
      ResponseInterface $response = NULL,
      \Exception $exception,
      QueueInterface $queue,
      URLHandler $handler
    ) {
        parent::__construct($request, $queue, $handler);
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