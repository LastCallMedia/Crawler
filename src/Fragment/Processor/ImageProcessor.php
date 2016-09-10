<?php

namespace LastCall\Crawler\Fragment\Processor;

use LastCall\Crawler\Common\AddsRequests;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\FragmentSubscription;
use LastCall\Crawler\Uri\MatcherInterface;
use LastCall\Crawler\Uri\NormalizerInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class ImageProcessor implements FragmentProcessorInterface
{
    use AddsRequests;

    public function getSubscribedMethods()
    {
        return [
            new FragmentSubscription($this, 'xpath',
                'descendant-or-self::img[@src]', 'processImages'),
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

    public function processImages(CrawlerResponseEvent $event, DomCrawler $crawler)
    {
        $urls = array_unique($crawler->extract('src'));
        $this->addRequests($urls, $event);
    }
}
