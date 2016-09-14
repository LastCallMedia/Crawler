<?php

namespace LastCall\Crawler\Handler\Discovery;

use LastCall\Crawler\Common\RedirectDetectionTrait;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add in URLs that are redirected to, as long as they are matched.
 */
class RedirectDiscoverer extends AbstractDiscoverer implements EventSubscriberInterface
{
    use RedirectDetectionTrait;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onResponse',
        ];
    }

    public function onResponse(CrawlerResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $response = $event->getResponse();
        if ($this->isRedirectResponse($response)) {
            $this->processUris($event, $dispatcher, [$response->getHeaderLine('Location')]);
        }
    }
}
