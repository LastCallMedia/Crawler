<?php

namespace LastCall\Crawler\Handler;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Detect HTML responses and redispatch them as HTML response events.
 */
class HtmlRedispatcher implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onEvent',
            CrawlerEvents::FAILURE => 'onEvent',
        ];
    }

    /**
     * Act on a response event.
     *
     * @param \LastCall\Crawler\Event\CrawlerRequestEvent                 $event
     * @param                                                             $eventName
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function onEvent(CrawlerRequestEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if ($event instanceof CrawlerResponseEvent) {
            // @todo: Detect HTML here.
            $htmlEvent = new CrawlerHtmlResponseEvent($event->getRequest(), $event->getResponse());
            $dispatcher->dispatch($eventName.'.html', $htmlEvent);

            foreach ($htmlEvent->getAdditionalRequests() as $request) {
                $event->addAdditionalRequest($request);
            }
        }
    }
}
