<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CrawlerUrisDiscoveredEvent extends CrawlerResponseEvent
{
    /**
     * @var \Psr\Http\Message\UriInterface[]
     */
    private $discoveredUris = [];

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        array $discoveredUris
    ) {
        parent::__construct($request, $response);
        $this->discoveredUris = $discoveredUris;
    }

    public function getDiscoveredUris()
    {
        return $this->discoveredUris;
    }
}
