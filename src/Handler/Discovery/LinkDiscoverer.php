<?php


namespace LastCall\Crawler\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use LastCall\Crawler\Url\URLHandler;

class LinkDiscoverer implements CrawlerHandlerInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onSuccess'
        ];
    }

    public function onSuccess(CrawlerResponseEvent $event) {
        if($dom = $event->getDom()) {
            $handler = $event->getUrlHandler();
            $urls = array_unique($dom->filterXPath('descendant-or-self::a[@href]')
                ->extract('href'));

            foreach($this->processUrls($urls, $handler) as $url) {
                $request = new Request('GET', $url);
                $event->addAdditionalRequest($request);
            }
        }
    }

    private function processUrls(array $urls, URLHandler $handler) {
        foreach($urls as $url) {
            if ($url = $handler->absolutizeUrl($url)) {
                if ($handler->includesUrl($url)) {
                    if ($handler->isCrawlable($url)) {
                        yield $handler->normalizeUrl($url);
                    }
                }
            }
        }
    }
}