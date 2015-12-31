<?php

namespace LastCall\Crawler\Configuration;

/**
 * A single crawler configuration.
 */
interface ConfigurationInterface
{
    /**
     * Get the HTTP client to be used for this configuration.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient();

    /**
     * Get the queue to be used for this configuration.
     *
     * @return \LastCall\Crawler\Queue\RequestQueueInterface
     */
    public function getQueue();

    /**
     * Get any event listeners to be used for this configuration.
     *
     * @return array
     */
    public function getListeners();

    /**
     * Get any event subscribers to be used for this configuration.
     *
     * @return array
     */
    public function getSubscribers();
}
