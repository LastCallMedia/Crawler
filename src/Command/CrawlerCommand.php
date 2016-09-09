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

abstract class CrawlerCommand extends Command
{
    private $loader;
    private $dispatcher;

    protected function getConfiguration($filename)
    {
        return $this->getLoader()->loadFile($filename);
    }

    protected function getLoader()
    {
        if (!$this->loader) {
            $this->loader = new PHPConfigurationLoader();
        }

        return $this->loader;
    }

    public function setLoader(ConfigurationLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    protected function getDispatcher()
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    protected function prepareConfiguration(ConfigurationInterface $configuration, InputInterface $input, OutputInterface $output)
    {
        if ($configuration instanceof OutputAwareInterface) {
            $configuration->setOutput($output);
        }
    }

    protected function prepareDispatcher(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher, InputInterface $input, OutputInterface $output)
    {
        $monitor = new CrawlMonitor($configuration->getQueue(), new SymfonyStyle($input, $output));
        $dispatcher->addSubscriber($monitor);
        $configuration->attachToDispatcher($dispatcher);
    }

    protected function getCrawler(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher)
    {
        return new Crawler($dispatcher, $configuration->getClient(), $configuration->getQueue());
    }
}
