<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use LastCall\Crawler\Queue\Driver\ArrayDriver;
use LastCall\Crawler\Queue\Driver\DriverInterface;
use LastCall\Crawler\Url\URLHandler;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Configuration extends AbstractConfiguration
{

    private $finalized;

    public function __construct($baseUrl = NULL)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client(['allow_redirects' => FALSE]);
        $this->queueDriver = new ArrayDriver();
        $this->urlHandler = new URLHandler($baseUrl);
        $this->dispatcher = new EventDispatcher();
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setUrlHandler(URLHandler $handler)
    {
        $this->urlHandler = $handler;
        return $this;
    }

    public function getUrlHandler()
    {
        return $this->urlHandler;
    }

    public function setQueueDriver(DriverInterface $driver)
    {
        $this->queueDriver = $driver;
        return $this;
    }

    public function getRequestQueue()
    {
        return $this->requestQueue;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setSubscribers(array $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber) {
        $this->subscribers[] = $subscriber;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function addListener($eventName, callable $callback) {
        $this->listeners[$eventName][] = $callback;
    }

    public function dispatch($eventName, Event $event = null)
    {
        if(!$this->finalized) {
            foreach($this->subscribers as $subscriber) {
                $this->dispatcher->addSubscriber($subscriber);
            }
            foreach($this->listeners as $en => $listeners) {
                foreach($listeners as $listener) {
                    $this->dispatcher->addListener($en, $listener);
                }
            }
            $this->finalized = TRUE;
        }
        return $this->dispatcher->dispatch($eventName, $event);
    }
}