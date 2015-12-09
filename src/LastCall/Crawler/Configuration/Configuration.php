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

    public function __construct($baseUrl = NULL)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client(['allow_redirects' => FALSE]);
        $this->queueDriver = new ArrayDriver();
        $this->urlHandler = new URLHandler($baseUrl);
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function setUrlHandler(URLHandler $handler)
    {
        $this->urlHandler = $handler;
        return $this;
    }

    public function setQueueDriver(DriverInterface $driver)
    {
        $this->queueDriver = $driver;
        return $this;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function setSubscribers(array $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber) {
        $this->subscribers[] = $subscriber;
    }

    public function addListener($eventName, callable $callback) {
        $this->listeners[$eventName][] = $callback;
    }
}