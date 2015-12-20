<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Promise\FulfilledPromise;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Helper\ConfigurationHelper;
use LastCall\Crawler\Session\SessionInterface;
use LastCall\Crawler\Test\Command\CommandTest;
use LastCall\Crawler\Test\Resources\DummyCrawlerHelper;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

class WorkCommandTest extends \PHPUnit_Framework_TestCase
{

    public function testRunsCrawler()
    {
        $configuration = new Configuration('https://lastcallmedia.com');
        $crawler = $this->prophesize(Crawler::class);
        $crawler->start(5, null)
            ->willReturn(new FulfilledPromise('foo'))
            ->shouldBeCalled();

        $helper = new DummyCrawlerHelper($configuration, null,
            $crawler->reveal());


        $command = new CrawlCommand();
        $command->setHelperSet(new HelperSet([$helper]));
        $tester = new CommandTester($command);
        $tester->execute([]);
    }

    public function testClearsCrawler()
    {
        $configuration = new Configuration('https://lastcallmedia.com');
        $session = $this->prophesize(SessionInterface::class);
        $session->onTeardown()->shouldBeCalled();
        $session->onSetup()->shouldBeCalled();

        $crawler = $this->prophesize(Crawler::class);
        $crawler->start(Argument::any(), Argument::any())
            ->willReturn(new FulfilledPromise('foo'));

        $helper = new DummyCrawlerHelper($configuration, $session->reveal(),
            $crawler->reveal());

        $command = new CrawlCommand();
        $command->setHelperSet(new HelperSet([$helper]));
        $tester = new CommandTester($command);
        $tester->execute(['--reset' => true]);
    }
}