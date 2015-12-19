<?php


namespace LastCall\Crawler\Test\Handler;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

trait HandlerTestTrait
{

    public function invokeEvent(
        EventSubscriberInterface $handler,
        $eventName,
        Event $event = null
    ) {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($handler);

        return $dispatcher->dispatch($eventName, $event);
    }
}