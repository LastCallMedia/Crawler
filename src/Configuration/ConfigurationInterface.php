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
     * @return \LastCall\Crawler\Queue\RequestQueueInterface
     */
    public function getQueue();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @return array
     */
    public function getListeners();

    /**
     * @return array
     */
    public function getSubscribers();
}