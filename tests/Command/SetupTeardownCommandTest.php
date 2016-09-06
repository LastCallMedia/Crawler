<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Factory\PreloadedConfigurationFactory;
use LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface;
use LastCall\Crawler\CrawlerEvents;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;

class SetupTeardownCommandTest extends \PHPUnit_Framework_TestCase
{
    public function getCommands()
    {
        return [
            [SetupTeardownCommand::setup(), false, true],
            [SetupTeardownCommand::teardown(), true, false],
            [SetupTeardownCommand::reset(), true, true],
        ];
    }

    private function getDummyLoader($config) {
        $loader = $this->prophesize(ConfigurationLoaderInterface::class);
        $loader->loadFile(Argument::any())->willReturn($config);
        return $loader->reveal();
    }

    public function testSetup()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $command = SetupTeardownCommand::setup();
        $command->setLoader($this->getDummyLoader($config));
        $this->assertCommandSetupTeardown($config, $command, true, false);
    }

    public function testTeardown()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $command = SetupTeardownCommand::teardown();
        $command->setLoader($this->getDummyLoader($config));
        $this->assertCommandSetupTeardown($config, $command, false, true);
    }

    public function testReset()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $command = SetupTeardownCommand::reset();
        $command->setLoader($this->getDummyLoader($config));
        $this->assertCommandSetupTeardown($config, $command, true, true);
    }

    protected function assertCommandSetupTeardown(ConfigurationInterface $configuration, SetupTeardownCommand $command, $setupExpected, $teardownExpected)
    {
        $setupCalled = $teardownCalled = false;
        $setupListener = function () use (&$setupCalled) {
            $setupCalled = true;
        };
        $teardownListener = function () use (&$teardownCalled) {
            $teardownCalled = true;
        };
        $configuration->addListener(CrawlerEvents::SETUP, $setupListener);
        $configuration->addListener(CrawlerEvents::TEARDOWN, $teardownListener);
        $tester = new CommandTester($command);
        $tester->execute([]);
        $this->assertEquals($setupExpected, $setupCalled);
        $this->assertEquals($teardownExpected, $teardownCalled);
    }
}
