<?php

namespace LastCall\Crawler\Handler\Discovery;

use LastCall\Crawler\Common\RedirectDetectionTrait;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Discovers URLs that are redirected to.
 */
class RedirectDiscoverer extends AbstractDiscoverer implements EventSubscriberInterface
{
    use RedirectDetectionTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onResponse',
        ];
    }

    /**
     * Discover a URL from a location header.
     *
     * @param \LastCall\Crawler\Event\CrawlerResponseEvent                $event
     * @param                                                             $eventName
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function onResponse(CrawlerResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $response = $event->getResponse();
        if ($this->isRedirectResponse($response)) {
            $this->processUris($event, $dispatcher, [$response->getHeaderLine('Location')]);
        }
    }
}
