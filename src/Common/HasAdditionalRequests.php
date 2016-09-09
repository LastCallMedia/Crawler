<?php

namespace LastCall\Crawler\Common;

use Psr\Http\Message\RequestInterface;

trait HasAdditionalRequests
{
    /**
     * @var RequestInterface[]
     */
    private $additionalRequests = [];

    /**
     * Add a request to crawl a single URL, specifying the request
     * that is used.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function addAdditionalRequest(RequestInterface $request)
    {
        $this->additionalRequests[] = $request;
    }

    /**
     * Get additional requests that have been added.
     *
     * @return \Psr\Http\Message\RequestInterface[]
     */
    public function getAdditionalRequests()
    {
        return $this->additionalRequests;
    }
}
