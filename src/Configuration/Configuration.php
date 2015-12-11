<?php

namespace LastCall\Crawler\Configuration;

use GuzzleHttp\Client;
use LastCall\Crawler\Queue\Driver\ArrayDriver;
use LastCall\Crawler\Queue\RequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Configuration extends AbstractConfiguration
{

    public function __construct($baseUrl = NULL)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client(['allow_redirects' => FALSE]);
        $this->queue = new RequestQueue(new ArrayDriver(), 'request');
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

    public function setQueue(RequestQueueInterface $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber) {
        $this->subscribers[] = $subscriber;
    }

    public function addListener($eventName, callable $listener, $priority = 0) {
        $this->listeners[$eventName][] = [$listener, $priority];
    }
}