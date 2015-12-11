<?php

namespace LastCall\Crawler\Listener;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LinkSubscriber implements EventSubscriberInterface
{

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CrawlerEvents::SUCCESS => array('onCrawlerSuccess'),
        );
    }

    public function onCrawlerSuccess(CrawlerResponseEvent $event)
    {
        $status = $event->getResponse()->getStatusCode();

        if ($status >= 200 && $status < 300) {
            if ($dom = $event->getDom()) {
                $urlHandler = $event->getUrlHandler();
                // Scan for links and files.  These would probably be better off in their
                // own subscribers, but are combined here for performance reasons.  DOM parsing
                // is expensive...
                $this->scanLinks($dom, $urlHandler, $event->getQueue());
            }
        }
    }

    private function scanLinks(
        DomCrawler $dom,
        URLHandler $urlHandler,
        RequestQueueInterface $queue
    ) {
        // This is the same as the CSS selector a[href].
        // Converted to xpath for performance.
        $urls = array_unique($dom->filterXPath('descendant-or-self::a[@href]')
            ->extract('href'));

        foreach ($urls as $url) {
            if ($url = $urlHandler->absolutizeUrl($url)) {
                if ($urlHandler->includesUrl($url)) {
                    if ($urlHandler->isCrawlable($url)) {
                        $normalUrl = $urlHandler->normalizeUrl($url);
                        $request = new Request('GET', $normalUrl);
                        $queue->push($request);
                    }
                }
            }
        }
    }
}