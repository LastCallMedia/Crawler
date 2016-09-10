<?php


namespace LastCall\Crawler\Common;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\MatcherInterface;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Uri\NormalizerInterface;
use Psr\Http\Message\UriInterface;

trait AddsRequests
{
    protected $matcher;

    protected $normalizer;

    protected $requestFactory;

    public function setMatcher(MatcherInterface $matcher) {
        $this->matcher = $matcher;
    }

    public function setNormalizer(NormalizerInterface $normalizer) {
        $this->normalizer = $normalizer;
    }

    public function setRequestFactory(callable $requestFactory = null) {
        if(!$requestFactory) {
            $requestFactory = function(UriInterface $uri) {
                return new Request('GET', $uri);
            };
        }
        $this->requestFactory = $requestFactory;
    }

    protected function addRequests(array $urls, CrawlerRequestEvent $event) {
        $request = $event->getRequest();
        $resolve = Normalizations::resolve($request->getUri());
        $factory = $this->requestFactory;

        foreach ($urls as $url) {
            $uri = new Uri($url);
            $uri = $resolve($uri);
            $uri = $this->normalizer->normalize($uri);

            if ($this->matcher->matches($uri) && $newRequest = $factory($uri)) {
                $event->addAdditionalRequest($newRequest);
            }
        }
    }

}