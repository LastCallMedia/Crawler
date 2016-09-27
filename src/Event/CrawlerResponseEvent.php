<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Wraps data for a request/response cycle.
 */
class CrawlerResponseEvent extends CrawlerRequestEvent
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        parent::__construct($request);
        $this->response = $response;
    }

    /**
     * Get the response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
