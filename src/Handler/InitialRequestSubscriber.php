<?php

namespace LastCall\Crawler\Handler;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerStartEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitialRequestSubscriber implements EventSubscriberInterface
{
    private $requests = [];

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::START => 'onStart',
        ];
    }

    public function __construct(array $requests = [])
    {
        $this->requests = $requests;
    }

    public function onStart(CrawlerStartEvent $event)
    {
        foreach ($this->requests as $request) {
            $event->addAdditionalRequest($request);
        }
    }
}
