<?php

namespace LastCall\Crawler\Configuration;

use Symfony\Component\Console\Output\OutputInterface;

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
     * Get the URL Handler to be used for this configuration.
     *
     * @return \LastCall\Crawler\Url\UrlHandler
     */
    public function getUrlHandler();

    /**
     * Get the queue to be used for this configuration.
     *
     * @return \LastCall\Crawler\Queue\RequestQueueInterface
     */
    public function getQueue();

    /**
     * Get the base URL to be used for this configuration.
     *
     * @return string
     */
    public function getBaseUrl();

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

    /**
     * Attach console output to the configuration.
     *
     * This function is not guaranteed to be called.  The configuration
     * should not error out if console output is not attached.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function attachOutput(OutputInterface $output);
}