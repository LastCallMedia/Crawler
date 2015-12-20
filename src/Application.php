<?php

namespace LastCall\Crawler;

use LastCall\Crawler\Command\ClearCommand;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Command\SetupCommand;
use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Command\TeardownCommand;
use LastCall\Crawler\Helper\CrawlerHelper;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Crawler application
 */
class Application extends BaseApplication
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     *      $dispatcher
     */
    private $dispatcher;

    public function __construct($name = 'LCM Crawler', $version = '1.0')
    {
        parent::__construct($name, $version);
    }

    /**
     * Get the default helper set.
     *
     * @return HelperSet
     */
    public function getDefaultHelperSet()
    {
        $helpers = parent::getDefaultHelperSet();
        $helpers->set(new CrawlerHelper());

        return $helpers;
    }

    /**
     * Get the default commands.
     *
     * @return Command[]
     */
    public function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), array(
            new CrawlCommand(),
            SetupTeardownCommand::setup(),
            SetupTeardownCommand::teardown(),
            SetupTeardownCommand::reset(),
        ));
    }

}