<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface;
use LastCall\Crawler\Configuration\Loader\PHPConfigurationLoader;
use LastCall\Crawler\Handler\CrawlMonitor;
use Symfony\Component\Console\Command\Command;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base crawler command methods used by concrete commands.
 */
abstract class CrawlerCommand extends Command
{
    private $loader;
    private $dispatcher;

    /**
     * Use the loader to load a configuration by filename.
     *
     * @param $filename
     *
     * @return mixed
     */
    protected function getConfiguration($filename)
    {
        return $this->getLoader()->loadFile($filename);
    }

    /**
     * Get the configuration loader.
     *
     * @return \LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface
     */
    protected function getLoader()
    {
        if (!$this->loader) {
            $this->loader = new PHPConfigurationLoader();
        }

        return $this->loader;
    }

    /**
     * Set the configuration loader.
     *
     * @param \LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface $loader
     */
    public function setLoader(ConfigurationLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Get the event dispatcher.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getDispatcher()
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Prepare a configuration for running on the console.
     *
     * @param \LastCall\Crawler\Configuration\ConfigurationInterface $configuration
     * @param \Symfony\Component\Console\Input\InputInterface        $input
     * @param \Symfony\Component\Console\Output\OutputInterface      $output
     */
    protected function prepareConfiguration(ConfigurationInterface $configuration, InputInterface $input, OutputInterface $output)
    {
        if ($configuration instanceof OutputAwareInterface) {
            $configuration->setOutput($output);
        }
    }

    /**
     * Prepare a dispatcher for use by a console command.
     *
     * @param \LastCall\Crawler\Configuration\ConfigurationInterface      $configuration
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \Symfony\Component\Console\Input\InputInterface             $input
     * @param \Symfony\Component\Console\Output\OutputInterface           $output
     */
    protected function prepareDispatcher(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher, InputInterface $input, OutputInterface $output)
    {
        $monitor = new CrawlMonitor($configuration->getQueue(), new SymfonyStyle($input, $output));
        $dispatcher->addSubscriber($monitor);
        $configuration->attachToDispatcher($dispatcher);
    }

    /**
     * Create a crawler instance.
     *
     * @param \LastCall\Crawler\Configuration\ConfigurationInterface      $configuration
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     *
     * @return \LastCall\Crawler\Crawler
     */
    protected function getCrawler(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher)
    {
        return new Crawler($dispatcher, $configuration->getClient(), $configuration->getQueue());
    }
}
