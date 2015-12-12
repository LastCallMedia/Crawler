<?php


namespace LastCall\Crawler\Test\Handler;


use LastCall\Crawler\Handler\CrawlerHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

trait HandlerTestTrait
{

    public function invokeEvent(CrawlerHandlerInterface $handler, $eventName, Event $event = NULL) {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($handler);
        return $dispatcher->dispatch($eventName, $event);
    }
}