<?php

namespace LastCall\Crawler\Fragment\Processor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\FragmentSubscription;
use LastCall\Crawler\Uri\MatcherInterface;
use LastCall\Crawler\Uri\NormalizerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkProcessor implements FragmentProcessorInterface
{
    public function getSubscribedMethods()
    {
        return [
            new FragmentSubscription($this, 'xpath',
                'descendant-or-self::a[@href]', 'processLinks'),
        ];
    }

    private $matcher;
    private $normalizer;

    public function __construct(
        MatcherInterface $matcher,
        NormalizerInterface $normalizer
    ) {
        $this->matcher = $matcher;
        $this->normalizer = $normalizer;
    }

    public function processLinks(
        CrawlerResponseEvent $event,
        DomCrawler $crawler
    ) {
        $urls = array_unique($crawler->extract(['href']));

        $request = $event->getRequest();

        foreach ($urls as $url) {
            if ($url = $this->absolutizeUrl($url, $request)) {
                if ($this->matcher->matches($url) && $this->matcher->matchesHtml($url)) {
                    $url = $this->normalizer->normalize($url);
                    $newRequest = new Request('GET', $url);
                    $event->addAdditionalRequest($newRequest);
                }
            }
        }
    }

    private function absolutizeUrl($url, RequestInterface $request)
    {
        if (is_string($url)) {
            if (strpos($url, 'http') === 0) {
                return $url;
            } elseif (strpos($url, '#') === 0) {
                return $request->getUri()->withFragment($url);
            } elseif (strpos($url, 'mailto:') === 0 || strpos($url,
                    'javascript:') === 0
            ) {
                return false;
            }
        } elseif ($url instanceof UriInterface && $url->getScheme()) {
            return $url;
        }

        return Uri::resolve($request->getUri(), $url);
    }
}
