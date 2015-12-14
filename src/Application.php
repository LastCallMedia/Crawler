<?php

namespace LastCall\Crawler;

use LastCall\Crawler\Command\ClearCommand;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Command\SetupCommand;
use LastCall\Crawler\Command\TeardownCommand;
use LastCall\Crawler\Helper\CrawlerHelper;
use LastCall\Crawler\Helper\ProfilerHelper;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Crawler application
 */
class Application extends BaseApplication
{

    public function getDefaultHelperSet()
    {
        $helpers = parent::getDefaultHelperSet();
        $helpers->set(new CrawlerHelper());
        $helpers->set(new ProfilerHelper());

        return $helpers;
    }

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