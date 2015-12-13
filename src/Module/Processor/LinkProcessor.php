<?php


namespace LastCall\Crawler\Module\Processor;


use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Module\ModuleSubscription;
use LastCall\Crawler\Url\URLHandler;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkProcessor implements ModuleProcessorInterface
{

    public function getSubscribedMethods()
    {
        return [
            new ModuleSubscription($this, 'xpath', 'descendant-or-self::a[@href]', 'processLinks')
        ];
    }

    public function processLinks(CrawlerResponseEvent $event, DomCrawler $crawler) {
        $urls = array_unique($crawler->extract(['href']));

        $handler = $event->getUrlHandler();

        foreach ($this->processUrls($urls, $handler) as $url) {
            $request = new Request('GET', $url);
            $event->addAdditionalRequest($request);
        }
    }

    public function processUrls(array $urls, URLHandler $handler) {
        foreach ($urls as $url) {
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