<?php


namespace LastCall\Crawler\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use LastCall\Crawler\Handler\RedirectDetectionTrait;

/**
 * Add in URLs that are redirected to, as long as they are matched
 * by the URLHandler.
 */
class RedirectDiscoverer implements CrawlerHandlerInterface
{
    use RedirectDetectionTrait;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CrawlerEvents::SUCCESS => 'onResponse',
        );
    }

    public function onResponse(CrawlerResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($this->isRedirectResponse($response)) {
            $urlHandler = $event->getUrlHandler();

            $location = $urlHandler->absolutizeUrl($response->getHeaderLine('Location'));
            if ($urlHandler->includesUrl($location) && $urlHandler->isCrawlable($location)) {
                $normalUrl = $urlHandler->normalizeUrl($location);
                $request = new Request('GET', $normalUrl);
                $event->addAdditionalRequest($request);
            }
        }
    }

}