<?php

namespace LastCall\Crawler\Configuration;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractConfiguration implements ConfigurationInterface
{

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \LastCall\Crawler\Url\UrlHandler
     */
    protected $urlHandler;

    /**
     * @var \LastCall\Crawler\Queue\Driver\DriverInterface
     */
    protected $queueDriver;

    /**
     * @var \Symfony\Component\EventDispatcher\EventSubscriberInterface[]
     */
    protected $subscribers = array();

    /**
     * @var array
     */
    protected $listeners = array();

    public function getClient()
    {
        return $this->client;
    }

    public function getUrlHandler()
    {
        return $this->urlHandler;
    }

    public function getQueueDriver()
    {
        return $this->queueDriver;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function getListeners() {
        return $this->listeners;
    }
}