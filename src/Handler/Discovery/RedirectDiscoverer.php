<?php

namespace LastCall\Crawler\Handler\Discovery;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Common\RedirectDetectionTrait;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Uri\MatcherInterface;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\NormalizerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add in URLs that are redirected to, as long as they are matched.
 */
class RedirectDiscoverer implements EventSubscriberInterface
{
    use RedirectDetectionTrait;

    /**
     * @var MatcherInterface
     */
    private $matcher;
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onResponse',
        ];
    }

    public function __construct(
        MatcherInterface $matcher,
        NormalizerInterface $normalizer
    ) {
        $this->matcher = $matcher;
        $this->normalizer = $normalizer;
    }

    public function onResponse(CrawlerResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->isRedirectResponse($response)) {
            $request = $event->getRequest();
            $resolve = Normalizations::resolve($request->getUri());

            $location = new Uri($response->getHeaderLine('Location'));
            $location = $resolve($location);
            $location = $this->normalizer->normalize($location);

            if ($this->matcher->matches($location)) {
                $request = new Request('GET', $location);
                $event->addAdditionalRequest($request);
            }
        }
    }
}
