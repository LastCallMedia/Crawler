<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Contains data about an exception during the crawl cycle.
 */
class CrawlerExceptionEvent extends CrawlerEvent
{

    private $exception;

    private $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $exception
    ) {
        parent::__construct($request);
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