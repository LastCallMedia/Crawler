<?php

namespace LastCall\Crawler;

use LastCall\Crawler\Command\ClearCommand;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Command\SetupCommand;
use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Command\TeardownCommand;
use LastCall\Crawler\Helper\InputAwareCrawlerHelper;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Crawler application
 */
class Application extends BaseApplication
{
    const VERSION = '1.0.3';

    public function __construct()
    {
        parent::__construct('LCM Crawler', self::VERSION);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('config', 'c',
            InputOption::VALUE_REQUIRED, 'Path to a configuration file.',
            'crawler.php'));

        return $definition;
    }

    /**
     * Get the default helper set.
     *
     * @return HelperSet
     */
    public function getDefaultHelperSet()
    {
        $helpers = parent::getDefaultHelperSet();
        $helpers->set(new InputAwareCrawlerHelper());

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
