<?php


namespace LastCall\Crawler\Helper;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Reporter\ReporterInterface;
use LastCall\Crawler\Session\SessionInterface;

interface CrawlerHelperInterface
{

    /**
     * Get the configuration.
     *
     * @param string          $filename
     * @param OutputInterface $output
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Create a crawler session for a configuration.
     *
     * @param \LastCall\Crawler\Configuration\ConfigurationInterface $config
     *
     * @return \LastCall\Crawler\Session\Session
     */
    public function getSession(
        ConfigurationInterface $config,
        ReporterInterface $reporter = null
    );

    /**
     * Get a crawler instance for a session.
     *
     * @param SessionInterface       $session
     * @param ConfigurationInterface $config
     *
     * @return \LastCall\Crawler\Crawler
     */
    public function getCrawler(
        ConfigurationInterface $config,
        SessionInterface $session
    );

}