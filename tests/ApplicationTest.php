<?php

namespace LastCall\Crawler\Test;


use LastCall\Crawler\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultCommands()
    {
        $application = new Application();
        $this->assertInstanceOf('LastCall\Crawler\Command\ClearCommand',
            $application->get('clear'));
        $this->assertInstanceOf('LastCall\Crawler\Command\SetupCommand',
            $application->get('setup'));
        $this->assertInstanceOf('LastCall\Crawler\Command\TeardownCommand',
            $application->get('teardown'));
        $this->assertInstanceOf('LastCall\Crawler\Command\CrawlCommand',
            $application->get('crawl'));
    }

    public function testDefaultHelpers()
    {
        $application = new Application();
        $this->assertInstanceOf('LastCall\Crawler\Helper\CrawlerHelper',
            $application->getHelperSet()->get('crawler'));
        $this->assertInstanceOf('LastCall\Crawler\Helper\ProfilerHelper',
            $application->getHelperSet()->get('profiler'));
    }
}