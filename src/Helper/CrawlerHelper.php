<?php

namespace LastCall\Crawler\Helper;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Session\Session;
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
     * Get a crawler instance for a given configuration.
     *
     * @param ConfigurationInterface $config
     * @param bool                   $profile
     *
     * @return \LastCall\Crawler\Crawler
     */
    public function getCrawler(
        ConfigurationInterface $config,
        $profile = false
    ) {
        $dispatcher = new EventDispatcher();
        if ($profile && $this->getHelperSet()->has('profiler')) {
            $profiler = $this->getHelperSet()->get('profiler');
            $dispatcher = $profiler->getTraceableDispatcher($dispatcher);
        }
        $session = new Session($config, $dispatcher);

        return new Crawler($session);
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