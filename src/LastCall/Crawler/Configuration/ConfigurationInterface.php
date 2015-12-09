<?php

namespace LastCall\Crawler\Configuration;


interface ConfigurationInterface
{

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient();

    /**
     * @return \LastCall\Crawler\Url\UrlHandler
     */
    public function getUrlHandler();

    /**
     * @return \LastCall\Crawler\Queue\Driver\DriverInterface
     */
    public function getQueueDriver();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @return \Symfony\Component\EventDispatcher\EventSubscriberInterface[]
     */
    public function getSubscribers();

    /**
     * @return array
     */
    public function getListeners();
}