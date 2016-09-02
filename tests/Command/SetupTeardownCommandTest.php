<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Factory\PreloadedConfigurationFactory;
use LastCall\Crawler\CrawlerEvents;
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

    public function testSetup()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $factory = new PreloadedConfigurationFactory($config);
        $command = SetupTeardownCommand::setup($factory);
        $this->assertCommandSetupTeardown($config, $command, true, false);
    }

    public function testTeardown()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $factory = new PreloadedConfigurationFactory($config);
        $command = SetupTeardownCommand::teardown($factory);
        $this->assertCommandSetupTeardown($config, $command, false, true);
    }

    public function testReset()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $factory = new PreloadedConfigurationFactory($config);
        $command = SetupTeardownCommand::reset($factory);
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
