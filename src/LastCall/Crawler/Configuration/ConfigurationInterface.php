<?php

namespace LastCall\Crawler\Configuration;


use Symfony\Component\EventDispatcher\Event;

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
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getDispatcher();

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

    public function dispatch($eventName, Event $event = NULL);
}