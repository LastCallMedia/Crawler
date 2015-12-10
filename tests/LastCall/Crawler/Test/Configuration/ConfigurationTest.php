<?php

namespace LastCall\Crawler\Test\Configuration;

use GuzzleHttp\Client;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Queue\Driver\DriverInterface;
use LastCall\Crawler\Url\URLHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testBaseUrl() {
        $config = new Configuration();
        $this->assertNull($config->getBaseUrl());
        $config->setBaseUrl('http://google.com');
        $this->assertEquals('http://google.com', $config->getBaseUrl());
        $config = new Configuration('http://google.com');
        $this->assertEquals('http://google.com', $config->getBaseUrl());
    }

    public function testClient() {
        $config = new Configuration();
        $this->assertInstanceOf('GuzzleHttp\Client', $config->getClient());
        $client = new Client();
        $config->setClient($client);
        $this->assertSame($client, $config->getClient());
    }

    public function testUrlHandler() {
        $config = new Configuration();
        $this->assertInstanceOf(URLHandler::class, $config->getUrlHandler());
        $handler = $this->prophesize(URLHandler::class)->reveal();
        $config->setUrlHandler($handler);
        $this->assertSame($handler, $config->getUrlHandler());
    }

    public function testQueueDriver() {
        $config = new Configuration();
        $this->assertInstanceOf(DriverInterface::class, $config->getQueueDriver());
        $queue = $this->prophesize(DriverInterface::class)->reveal();
        $config->setQueueDriver($queue);
        $this->assertSame($queue, $config->getQueueDriver());
    }

    public function testSubscribers() {
        $config = new Configuration();
        $this->assertEquals([], $config->getSubscribers());
        $subscriber = $this->prophesize(EventSubscriberInterface::class)->reveal();
        $config->addSubscriber($subscriber);
        $this->assertSame($subscriber, $config->getSubscribers()[0]);
    }

    public function testListeners() {
        $config = new Configuration();
        $this->assertEquals([], $config->getListeners());
        $listener = function() {};
        $config->addListener('foo', $listener);
        $this->assertSame($listener, $config->getListeners()['foo'][0]);
    }
}