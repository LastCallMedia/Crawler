<?php

namespace LastCall\Crawler\Fragment\Processor;

use LastCall\Crawler\Common\AddsRequests;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\FragmentSubscription;
use LastCall\Crawler\Uri\MatcherInterface;
use LastCall\Crawler\Uri\NormalizerInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class AssetProcessor implements FragmentProcessorInterface
{
    use AddsRequests;

    public function getSubscribedMethods()
    {
        return [
            new FragmentSubscription($this, 'xpath',
                'descendant-or-self::link[@rel = "stylesheet" and (@href)]', 'processStylesheets'),
            new FragmentSubscription($this, 'xpath',
                'descendant-or-self::script[@type="text/javascript"]', 'processScripts'),
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

    public function processStylesheets(CrawlerResponseEvent $event, DomCrawler $crawler)
    {
        $urls = array_unique($crawler->extract('href'));
        $this->addRequests($urls, $event);
    }

    public function processScripts(CrawlerResponseEvent $event, DomCrawler $crawler)
    {
        $urls = array_unique($crawler->extract('src'));
        $this->addRequests($urls, $event);
    }
}
