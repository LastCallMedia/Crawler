<?php

namespace LastCall\Crawler\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Wraps data for a URL discovery event.
 *
 * This event is used to pass URLs discovered in the response to handlers.
 * It can be used to log the discovered URLs, or add them back into the
 * queue via the `addAdditionalRequest` method.
 */
class CrawlerUrisDiscoveredEvent extends CrawlerResponseEvent
{
    /**
     * @var \Psr\Http\Message\UriInterface[]
     */
    private $discoveredUris = [];

    /**
     * @var string
     */
    private $context;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        array $discoveredUris,
        $context = 'unknown'
    ) {
        parent::__construct($request, $response);
        $this->discoveredUris = $discoveredUris;
        $this->context = $context;
    }

    public function getDiscoveredUris()
    {
        return $this->discoveredUris;
    }

    public function getContext() {
        return $this->context;
    }
}
