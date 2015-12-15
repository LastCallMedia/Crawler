<?php

namespace LastCall\Crawler\Helper;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Session\SessionInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Console helper for instantiating crawlers and configurations
 */
class CrawlerHelper extends Helper
{

    /**
     * Get the name of the helper.
     *
     * @return string
     */
    public function getName()
    {
        return 'crawler';
    }

    /**
     * Get a crawler instance for a session.
     *
     * @param ConfigurationInterface $config
     *
     * @return \LastCall\Crawler\Crawler
     */
    public function getCrawler(
        SessionInterface $session,
        ConfigurationInterface $config
    ) {

        return new Crawler($session, $config->getClient());
    }

    /**
     * Create a crawler session for a configuration.
     *
     * @param \LastCall\Crawler\Configuration\ConfigurationInterface $config
     * @param bool                                                   $profile
     *
     * @return \LastCall\Crawler\Session\Session
     */
    public function getSession(ConfigurationInterface $config, $profile = false)
    {
        $dispatcher = new EventDispatcher();
        if ($profile && $this->getHelperSet()->has('profiler')) {
            $profiler = $this->getHelperSet()->get('profiler');
            $dispatcher = $profiler->getTraceableDispatcher($dispatcher);
        }

        return new Session($config, $dispatcher);
    }

    /**
     * Open and return a configuration file.
     *
     * @param string          $filename
     * @param OutputInterface $output
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration($filename, OutputInterface $output)
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException(sprintf('File does not exist: %s',
                $filename));
        }
        $configuration = require $filename;
        if ($configuration === 1) {
            throw new \RuntimeException('Configuration was not returned.');
        }
        if (!$configuration instanceof ConfigurationInterface) {
            throw new \RuntimeException(sprintf('Configuration must implement %s',
                ConfigurationInterface::class));
        }

        return $configuration;
    }
}