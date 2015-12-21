<?php


namespace LastCall\Crawler\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Common\RedirectDetectionTrait;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Url\URLHandler;
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

    private $urlHandler;

    public function __construct(URLHandler $urlHandler)
    {
        $this->urlHandler = $urlHandler;
    }

    public function onResponse(CrawlerResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->isRedirectResponse($response)) {
            $request = $event->getRequest();
            $urlHandler = $this->urlHandler->forUrl($request->getUri());

            $location = $response->getHeaderLine('Location');
            $location = Uri::resolve($request->getUri(), $location);

            if ($urlHandler->includesUrl($location) && $urlHandler->isCrawlable($location)) {
                $normalUrl = $urlHandler->normalizeUrl($location);
                $request = new Request('GET', $normalUrl);
                $event->addAdditionalRequest($request);
            }
        }
    }

}