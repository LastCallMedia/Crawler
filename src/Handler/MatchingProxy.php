<?php

namespace LastCall\Crawler\Handler;

use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MatchingProxy implements EventSubscriberInterface
{
    private $proxied;
    private $matcher;

    public function __construct(EventSubscriberInterface $subscriber, callable $matcher)
    {
        $this->proxied = $subscriber;
        $this->matcher = $matcher;
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($subscriber);
    }

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SETUP => 'proxyEvent',
            CrawlerEvents::TEARDOWN => 'proxyEvent',
            CrawlerEvents::FINISH => 'proxyEvent',
            CrawlerEvents::SENDING => 'checkAndProxyEvent',
            CrawlerEvents::SUCCESS => 'checkAndProxyEvent',
            CrawlerEvents::FAILURE => 'checkAndProxyEvent',
            CrawlerEvents::EXCEPTION => 'checkAndProxyEvent',
        ];
    }

    public function proxyEvent(Event $event, $name)
    {
        $this->dispatcher->dispatch($name, $event);
    }

    public function checkAndProxyEvent(CrawlerEvent $event, $name)
    {
        $uri = $event->getRequest()->getUri();
        $matcher = $this->matcher;
        if ($matcher($uri)) {
            $this->dispatcher->dispatch($name, $event);
        }
    }
}
