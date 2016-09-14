<?php

namespace LastCall\Crawler\Handler\Discovery;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetDiscoverer extends AbstractDiscoverer implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS_HTML => [
                ['discoverImages'],
                ['discoverStylesheets'],
                ['discoverScripts'],
            ],
            CrawlerEvents::FAILURE_HTML => [
                ['discoverImages'],
                ['discoverStylesheets'],
                ['discoverScripts'],
            ],
        ];
    }

    public function discoverImages(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();
        $nodes = $crawler->filterXPath('descendant-or-self::img[@src]');
        $urls = $nodes->extract('src');
        $this->processUris($event, $dispatcher, $urls);
    }

    public function discoverStylesheets(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();
        $nodes = $crawler->filterXPath('descendant-or-self::link[@rel = "stylesheet" and (@href)]');
        $urls = $nodes->extract('href');
        $this->processUris($event, $dispatcher, $urls);
    }

    public function discoverScripts(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();
        $nodes = $crawler->filterXPath('descendant-or-self::script[@type = "text/javascript" and (@src)]');
        $urls = $nodes->extract('src');
        $this->processUris($event, $dispatcher, $urls);
    }
}
