<?php

namespace LastCall\Crawler\Handler\Discovery;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LinkDiscoverer extends AbstractDiscoverer implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS_HTML => 'discoverLinks',
            CrawlerEvents::FAILURE_HTML => 'discoverLinks',
        ];
    }

    public function discoverLinks(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();

        $nodes = $crawler->filterXPath('descendant-or-self::a[@href]');
        $urls = array_unique($nodes->extract('href'));
        $this->processUris($event, $dispatcher, $urls);
    }
}
