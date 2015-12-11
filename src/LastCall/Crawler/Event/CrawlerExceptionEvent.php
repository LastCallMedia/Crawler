<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Queue\RequestQueueInterface;
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
      RequestQueueInterface $queue,
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