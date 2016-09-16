<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CrawlCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRunsCrawler()
    {
        $started = $finished = 0;
        $client = $this->prophesize(ClientInterface::class);
        $config = $this->prophesize(ConfigurationInterface::class);
        $config->getQueue()->willReturn(new ArrayRequestQueue());
        $config->getClient()->willReturn($client);
        $config->attachToDispatcher(Argument::type(EventDispatcherInterface::class))
            ->will(function($args) use (&$started, &$finished) {
                $args[0]->addListener(CrawlerEvents::START, function() use (&$started) {
                    $started++;
                });
                $args[0]->addListener(CrawlerEvents::FINISH, function() use (&$finished) {
                    $finished++;
                });
            });

        $loader = $this->prophesize(ConfigurationLoaderInterface::class);
        $loader->loadFile('crawler.php')->willReturn($config);

        $command = new CrawlCommand();
        $command->setLoader($loader->reveal());
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertEquals(1, $started);
        $this->assertEquals(1, $finished);
    }

    public function testExecutesReset()
    {

        $client = $this->prophesize(ClientInterface::class);
        $config = $this->prophesize(ConfigurationInterface::class);
        $config->getClient()->willReturn($client->reveal());
        $config->getQueue()->willReturn(new ArrayRequestQueue());
        $config->attachToDispatcher(Argument::type(EventDispatcherInterface::class))
            ->will(function($args) use (&$setup, &$teardown) {
                $args[0]->addListener(CrawlerEvents::SETUP, function() use (&$setup) {
                    $setup++;
                });
                $args[0]->addListener(CrawlerEvents::TEARDOWN, function() use (&$teardown) {
                    $teardown++;
                });
            });

        $loader = $this->prophesize(ConfigurationLoaderInterface::class);
        $loader->loadFile('crawler.php')->willReturn($config->reveal());

        $command = new CrawlCommand();
        $command->setLoader($loader->reveal());
        $tester = new CommandTester($command);
        $tester->execute(['--reset' => true]);

        $this->assertEquals(1, $setup);
        $this->assertEquals(1, $teardown);
    }
}
