<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Promise\FulfilledPromise;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Factory\ConfigurationFactoryInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Test\Resources\DummyCrawlCommand;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class WorkCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRunsCrawler()
    {
        $config = $this->prophesize(ConfigurationInterface::class);
        $factory = $this->prophesize(ConfigurationFactoryInterface::class);
        $factory->getConfiguration(Argument::type(InputInterface::class))
            ->willReturn($config);
        $factory->getName()->willReturn('crawl');
        $factory->getDescription()->willReturn('');
        $factory->getHelp()->willReturn('');
        $factory->configureInput(Argument::type(InputDefinition::class))->shouldBeCalled();
        $factory->getChunk(Argument::type(InputInterface::class))->willReturn(50);

        $crawler = $this->prophesize(Crawler::class);
        $crawler->start(50)
            ->willReturn(new FulfilledPromise('foo'))
            ->shouldBeCalled();

        $command = new DummyCrawlCommand($factory->reveal());
        $command->setCrawler($crawler->reveal());
        $tester = new CommandTester($command);
        $tester->execute([]);
    }
}
