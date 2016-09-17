<?php

namespace LastCall\Crawler\Test\Command;

use GuzzleHttp\ClientInterface;
use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SetupTeardownCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testSetup()
    {
        $this->assertCommandSetupTeardown(SetupTeardownCommand::setup(), 1, 0);
    }

    public function testTeardown()
    {
        $this->assertCommandSetupTeardown(SetupTeardownCommand::teardown(), 0, 1);
    }

    public function testReset()
    {
        $this->assertCommandSetupTeardown(SetupTeardownCommand::reset(), 1, 1);
    }

    public function assertCommandSetupTeardown($command, $expectedSetup, $expectedTeardown)
    {
        $setup = $teardown = 0;
        $config = $this->prophesize(ConfigurationInterface::class);
        $config->getClient()->willReturn($this->prophesize(ClientInterface::class));
        $config->getQueue()->willReturn($this->prophesize(RequestQueueInterface::class));
        $config->attachToDispatcher(Argument::type(EventDispatcherInterface::class))
            ->will(function ($args) use (&$setup, &$teardown) {
                $args[0]->addListener(CrawlerEvents::SETUP, function () use (&$setup) {
                    ++$setup;
                });
                $args[0]->addListener(CrawlerEvents::TEARDOWN, function () use (&$teardown) {
                    ++$teardown;
                });
            });
        $loader = $this->prophesize(ConfigurationLoaderInterface::class);
        $loader->loadFile(Argument::any())->willReturn($config);
        $command->setLoader($loader->reveal());
        (new CommandTester($command))->execute([]);
        $this->assertEquals($expectedSetup, $setup);
        $this->assertEquals($expectedTeardown, $teardown);
    }
}
