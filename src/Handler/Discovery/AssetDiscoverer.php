<?php

namespace LastCall\Crawler\Handler\Discovery;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Discovers asset (CSS/JS/image) URLs in an HTML response.
 */
class AssetDiscoverer extends AbstractDiscoverer implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * Discover image URLs.
     *
     * @param \LastCall\Crawler\Event\CrawlerHtmlResponseEvent            $event
     * @param                                                             $eventName
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function discoverImages(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();
        $nodes = $crawler->filterXPath('descendant-or-self::img[@src]');
        $urls = $nodes->extract('src');
        $this->processUris($event, $dispatcher, $urls, 'image');
    }

    /**
     * Discover stylesheet URLs.
     *
     * @param \LastCall\Crawler\Event\CrawlerHtmlResponseEvent            $event
     * @param                                                             $eventName
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function discoverStylesheets(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();
        $nodes = $crawler->filterXPath('descendant-or-self::link[@rel = "stylesheet" and (@href)]');
        $urls = $nodes->extract('href');
        $this->processUris($event, $dispatcher, $urls, 'stylesheet');
    }

    /**
     * Discover script URLs.
     *
     * @param \LastCall\Crawler\Event\CrawlerHtmlResponseEvent            $event
     * @param                                                             $eventName
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function discoverScripts(CrawlerHtmlResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $crawler = $event->getDomCrawler();
        $nodes = $crawler->filterXPath('descendant-or-self::script[@type = "text/javascript" and (@src)]');
        $urls = $nodes->extract('src');
        $this->processUris($event, $dispatcher, $urls, 'script');
    }
}
