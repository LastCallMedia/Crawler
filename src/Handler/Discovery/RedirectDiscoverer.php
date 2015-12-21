<?php


namespace LastCall\Crawler\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Common\RedirectDetectionTrait;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Url\MatcherInterface;
use LastCall\Crawler\Url\NormalizerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add in URLs that are redirected to, as long as they are matched
 * by the URLHandler.
 */
class RedirectDiscoverer implements EventSubscriberInterface
{
    use RedirectDetectionTrait;

    public static function getSubscribedEvents()
    {
        return array(
            CrawlerEvents::SUCCESS => 'onResponse',
        );
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

            $location = $response->getHeaderLine('Location');
            $location = Uri::resolve($request->getUri(), $location);

            if ($this->matcher->matches($location) && $this->matcher->matchesHtml($location)) {
                $normalUrl = $this->normalizer->normalize($location);
                $request = new Request('GET', $normalUrl);
                $event->addAdditionalRequest($request);
            }
        }
    }

}