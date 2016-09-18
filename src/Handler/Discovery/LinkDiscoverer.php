<?php

namespace LastCall\Crawler\Handler\Discovery;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Discovers link URLs in an HTML response.
 */
class LinkDiscoverer extends AbstractDiscoverer implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS_HTML => 'discoverLinks',
            CrawlerEvents::FAILURE_HTML => 'discoverLinks',
        ];
    }

    /**
     * Discover link URLS from anchor tags.
     *
     * @param \LastCall\Crawler\Event\CrawlerHtmlResponseEvent            $event
     * @param                                                             $eventName
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function discoverLinks(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();

        $nodes = $crawler->filterXPath('descendant-or-self::a[@href]');
        $urls = array_unique($nodes->extract('href'));
        $this->processUris($event, $dispatcher, $urls, 'link');
    }
}
