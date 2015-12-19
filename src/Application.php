<?php

namespace LastCall\Crawler;

use LastCall\Crawler\Command\ClearCommand;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Command\SetupCommand;
use LastCall\Crawler\Command\TeardownCommand;
use LastCall\Crawler\Helper\CrawlerHelper;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Crawler application
 */
class Application extends BaseApplication
{

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
            new SetupCommand(),
            new TeardownCommand(),
            new CrawlCommand(),
            new ClearCommand()
        ));
    }

}