<?php

namespace LastCall\Crawler\Test;


use LastCall\Crawler\Application;
use LastCall\Crawler\Command\SetupTeardownCommand;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultCommands()
    {
        $application = new Application();
        $this->assertInstanceOf(SetupTeardownCommand::class,
            $application->get('reset'));
        $this->assertInstanceOf(SetupTeardownCommand::class,
            $application->get('setup'));
        $this->assertInstanceOf(SetupTeardownCommand::class,
            $application->get('teardown'));
        $this->assertInstanceOf('LastCall\Crawler\Command\CrawlCommand',
            $application->get('crawl'));
    }

    public function testDefaultHelpers()
    {
        $application = new Application();
        $this->assertInstanceOf('LastCall\Crawler\Helper\CrawlerHelper',
            $application->getHelperSet()->get('crawler'));
    }
}