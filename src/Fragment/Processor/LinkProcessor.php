<?php

namespace LastCall\Crawler\Fragment\Processor;

use LastCall\Crawler\Common\AddsRequests;
use LastCall\Crawler\Uri\MatcherInterface;
use LastCall\Crawler\Uri\NormalizerInterface;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\FragmentSubscription;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkProcessor implements FragmentProcessorInterface
{
    use AddsRequests;

    public function getSubscribedMethods()
    {
        return [
            new FragmentSubscription($this, 'xpath',
                'descendant-or-self::a[@href]', 'processLinks'),
        ];
    }

    public function __construct(
        MatcherInterface $matcher,
        NormalizerInterface $normalizer,
        callable $requestFactory = null
    ) {
        $this->setMatcher($matcher);
        $this->setNormalizer($normalizer);
        $this->setRequestFactory($requestFactory);
    }

    public function processLinks(
        CrawlerResponseEvent $event,
        DomCrawler $crawler
    ) {
        $urls = array_unique($crawler->extract(['href']));
        $this->addRequests($urls, $event);
    }
}
