<?php

namespace LastCall\Crawler\Handler;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HtmlRedispatcher implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SUCCESS => 'onEvent',
            CrawlerEvents::FAILURE => 'onEvent',
        ];
    }

    public function onEvent(CrawlerRequestEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if ($event instanceof CrawlerResponseEvent) {
            $htmlEvent = new CrawlerHtmlResponseEvent($event->getRequest(), $event->getResponse());
            $dispatcher->dispatch($eventName.'.html', $htmlEvent);

            foreach ($htmlEvent->getAdditionalRequests() as $request) {
                $event->addAdditionalRequest($request);
            }
        }
    }
}
