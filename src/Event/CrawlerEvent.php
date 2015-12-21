<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\Event;


class CrawlerEvent extends Event
{

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    /**
     * @var \LastCall\Crawler\Url\URLHandler
     */
    private $urlHandler;

    /**
     * @var \Psr\Http\Message\RequestInterface[]
     */
    private $discoveredRequests = [];

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Add a request to crawl a single URL, specifying the request
     * that is used.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function addAdditionalRequest(RequestInterface $request)
    {
        $this->discoveredRequests[] = $request;
    }

    /**
     * Get the URLs that were discovered during handling the event.
     *
     * @return \Psr\Http\Message\RequestInterface[]
     */
    public function getAdditionalRequests()
    {
        return $this->discoveredRequests;
    }
}