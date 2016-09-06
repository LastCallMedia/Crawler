<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface;
use LastCall\Crawler\Configuration\Loader\PHPConfigurationLoader;
use LastCall\Crawler\Session\SessionInterface;
use Symfony\Component\Console\Command\Command;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Session\Session;
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

    protected function getSession(ConfigurationInterface $configuration, EventDispatcherInterface $dispatcher)
    {
        return Session::createFromConfig($configuration, $dispatcher);
    }

    protected function getCrawler(ConfigurationInterface $configuration, SessionInterface $session)
    {
        return new Crawler($session, $configuration->getClient(), $configuration->getQueue());
    }
}
