<?php

namespace LastCall\Crawler\Configuration;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function attachToDispatcher(EventDispatcherInterface $dispatcher);
}
