<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Wraps data for a request cycle that resulted in an exception.
 */
class CrawlerExceptionEvent extends CrawlerRequestEvent
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

    /**
     * Get the response object, if one exists.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the exception object.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
