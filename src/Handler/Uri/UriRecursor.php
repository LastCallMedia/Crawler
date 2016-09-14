<?php

namespace LastCall\Crawler\Handler\Uri;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Uri\MatcherInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UriRecursor implements EventSubscriberInterface
{
    /**
     * @var \LastCall\Crawler\Uri\MatcherInterface
     */
    private $matcher;

    /**
     * @var callable
     */
    private $requestFactory;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::URIS_DISCOVERED => 'onDiscovery',
        ];
    }

    public function __construct(MatcherInterface $matcher, callable $requestFactory = null)
    {
        $this->matcher = $matcher;
        if (!$requestFactory) {
            $requestFactory = function (UriInterface $uri) {
                return new Request('GET', $uri);
            };
        }
        $this->requestFactory = $requestFactory;
    }

    public function onDiscovery(CrawlerUrisDiscoveredEvent $event)
    {
        $factory = $this->requestFactory;

        foreach ($event->getDiscoveredUris() as $uri) {
            if ($this->matcher->matches($uri) && $request = $factory($uri)) {
                $event->addAdditionalRequest($request);
            }
        }
    }
}
