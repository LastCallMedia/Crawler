<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Promise\FulfilledPromise;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Helper\ConfigurationHelper;
use LastCall\Crawler\Test\Command\CommandTest;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;

class WorkCommandTest extends CommandTest
{

    public function testRunsCrawler()
    {
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $crawler = $this->prophesize(Crawler::class);
        $crawler->start(5, null)->willReturn(new FulfilledPromise('foo'))->shouldBeCalled();

        $command = new CrawlCommand();
        $command->setHelperSet($this->getMockHelperSet($configuration, $crawler));
        $tester = new CommandTester($command);
        $tester->execute(array('config' => 'test.php'));
    }

    public function testClearsCrawler() {
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $crawler = $this->prophesize(Crawler::class);
        $crawler->teardown()->shouldBeCalled();
        $crawler->setUp()->shouldBeCalled();
        $crawler->start(Argument::any(), Argument::any())->willReturn(new FulfilledPromise('foo'));

        $command = new CrawlCommand();
        $command->setHelperSet($this->getMockHelperSet($configuration, $crawler));
        $tester = new CommandTester($command);
        $tester->execute(array('config' => 'test.php', '--reset' => TRUE));
    }

    public function testInvokesProfiler() {
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $crawler = $this->prophesize(Crawler::class);
        $crawler->start(Argument::any(), Argument::any())->willReturn(new FulfilledPromise('foo'));

        $command = new CrawlCommand();
        $command->setHelperSet($this->getMockHelperSet($configuration, $crawler, TRUE));
        $tester = new CommandTester($command);
        $tester->execute(['config' => 'test.php', '--profile' => TRUE]);
    }

}