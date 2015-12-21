<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Contains data about a response that was received by the crawler.
 */
class CrawlerResponseEvent extends CrawlerEvent
{

    private $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        parent::__construct($request);
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

}