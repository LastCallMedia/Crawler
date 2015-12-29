<?php

namespace LastCall\Crawler\Test\Resources;

use LastCall\Crawler\CrawlerEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DummyEventSubscriber implements EventSubscriberInterface
{
    private $calls = [];

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SETUP => 'onEvent',
            CrawlerEvents::TEARDOWN => 'onEvent',
            CrawlerEvents::FINISH => 'onEvent',
            CrawlerEvents::SENDING => 'onEvent',
            CrawlerEvents::SUCCESS => 'onEvent',
            CrawlerEvents::FAILURE => 'onEvent',
            CrawlerEvents::EXCEPTION => 'onEvent',
        ];
    }

    public function onEvent(Event $event, $name)
    {
        if (!isset($this->calls[$name])) {
            $this->calls[$name] = 0;
        }
        ++$this->calls[$name];
    }

    public function getCalls()
    {
        return $this->calls;
    }
}
