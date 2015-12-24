<?php

namespace LastCall\Crawler\Configuration;

/**
 * A partially prefabricated configuration object to extend.
 */
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
     * @var \LastCall\Crawler\Queue\RequestQueueInterface
     */
    protected $queue;

    protected $subscribers = [];

    protected $listeners = [];

    public function getClient()
    {
        return $this->client;
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
