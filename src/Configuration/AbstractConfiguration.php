<?php

namespace LastCall\Crawler\Configuration;

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
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    protected $queue;

    protected $subscribers = array();

    protected $listeners = array();


    public function getClient()
    {
        return $this->client;
    }

    public function getUrlHandler()
    {
        return $this->urlHandler;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function getListeners()
    {
        return $this->listeners;
    }
}