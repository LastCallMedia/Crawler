<?php

namespace LastCall\Crawler\Test\Configuration;

use GuzzleHttp\Client;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testBaseUrl()
    {
        $config = new Configuration();
        $this->assertNull($config->getBaseUrl());
        $config->setBaseUrl('http://google.com');
        $this->assertEquals('http://google.com', $config->getBaseUrl());
        $config = new Configuration('http://google.com');
        $this->assertEquals('http://google.com', $config->getBaseUrl());
    }

    public function testClient()
    {
        $config = new Configuration();
        $this->assertInstanceOf('GuzzleHttp\Client', $config->getClient());
        $client = new Client();
        $config->setClient($client);
        $this->assertSame($client, $config->getClient());
    }

    public function testUrlHandler()
    {
        $config = new Configuration();
        $this->assertInstanceOf(URLHandler::class, $config->getUrlHandler());
        $handler = $this->prophesize(URLHandler::class)->reveal();
        $config->setUrlHandler($handler);
        $this->assertSame($handler, $config->getUrlHandler());
    }

    public function testQueue()
    {
        $config = new Configuration();
        $this->assertInstanceOf(RequestQueueInterface::class,
            $config->getQueue());
        $queue = $this->prophesize(RequestQueueInterface::class)->reveal();
        $config->setQueue($queue);
        $this->assertSame($queue, $config->getQueue());
    }

    public function testSubscribers()
    {
        $config = new Configuration('http://google.com');
        $this->assertEquals([], $config->getSubscribers());

        $subscriber = $this->prophesize(EventSubscriberInterface::class)
            ->reveal();
        $config->addSubscriber($subscriber);
        $this->assertSame([$subscriber], $config->getSubscribers());
    }

    public function testListeners()
    {
        $config = new Configuration('http://google.com');
        $this->assertEquals([], $config->getListeners());

        $listener = function () {
        };
        $config->addListener('foo', $listener, 10);
        $this->assertEquals(['foo' => [[$listener, 10]]],
            $config->getListeners());
    }

    public function testAttachOutput()
    {
        $success = false;
        $config = new Configuration();
        $injectedOutput = new NullOutput();
        $fn = function (OutputInterface $output) use (
            $injectedOutput,
            &$success
        ) {
            $this->assertSame($injectedOutput, $output);
            $success = true;
        };
        $config->onAttachOutput($fn);
        $config->setOutput($injectedOutput);
        $this->assertTrue($success);
    }
}