<?php

namespace LastCall\Crawler\Listener;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RedirectSubscriber implements EventSubscriberInterface
{

    public static $redirectCodes = array(201, 301, 302, 303, 307, 308);

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
          Crawler::SUCCESS => 'onResponse',
        );
    }

    public function onResponse(CrawlerResponseEvent $event)
    {
        $response = $event->getResponse();

        if (in_array($response->getStatusCode(),
            self::$redirectCodes) && $response->hasHeader('Location')
        ) {
            $urlHandler = $event->getUrlHandler();
            $queue = $event->getQueue();

            $location = $urlHandler->absolutizeUrl($response->getHeaderLine('Location'));
            if ($urlHandler->includesUrl($location) && $urlHandler->isCrawlable($location)) {
                $normalUrl = $urlHandler->normalizeUrl($location);
                $request = new Request('GET', $normalUrl);
                $queue->push($request);
            }
        }
    }
}