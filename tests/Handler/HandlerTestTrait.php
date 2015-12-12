<?php


namespace LastCall\Crawler\Test\Handler;


use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait HandlerTestTrait
{

    public function invokeEvent(
        CrawlerHandlerInterface $handler,
        $eventName,
        Event $event = null
    ) {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($handler);

        return $dispatcher->dispatch($eventName, $event);
    }
}