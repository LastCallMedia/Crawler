<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\Loader\ConfigurationLoaderInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use Symfony\Component\Console\Tester\CommandTester;

class CrawlCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRunsCrawler()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $config['client'] = function () {
            $handler = new MockHandler([new Response(200)]);

            return new Client(['handler' => HandlerStack::create($handler)]);
        };

        $config['queue'] = function () {
            return new ArrayRequestQueue([new Request('GET', 'https://lastcallmedia.com')]);
        };

        $loader = $this->prophesize(ConfigurationLoaderInterface::class);
        $loader->loadFile('crawler.php')->willReturn($config);

        $command = new CrawlCommand();
        $command->setLoader($loader->reveal());
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertEquals(1, $config['queue']->count(ArrayRequestQueue::COMPLETE));
    }

    public function testExecutesReset()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $config['client'] = function () {
            $handler = new MockHandler([new Response(200)]);

            return new Client(['handler' => HandlerStack::create($handler)]);
        };

        $config['queue'] = function () {
            return new ArrayRequestQueue([new Request('GET', 'https://lastcallmedia.com')]);
        };

        $loader = $this->prophesize(ConfigurationLoaderInterface::class);
        $loader->loadFile('crawler.php')->willReturn($config);

        $setup = $teardown = 0;
        $config->addListener(CrawlerEvents::SETUP, function () use (&$setup) {
            ++$setup;
        });
        $config->addListener(CrawlerEvents::TEARDOWN, function () use (&$teardown) {
            ++$teardown;
        });

        $command = new CrawlCommand();
        $command->setLoader($loader->reveal());
        $tester = new CommandTester($command);
        $tester->execute(['--reset' => true]);

        $this->assertEquals(1, $setup);
        $this->assertEquals(1, $teardown);
    }
}
