<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use Doctrine\DBAL\DriverManager;
use LastCall\Crawler\Configuration\ServiceProvider\QueueServiceProvider;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Pimple\Container;

class QueueServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultQueue()
    {
        $container = new Container();
        $container['listeners'] = function() {
            return [];
        };
        $container->register(new QueueServiceProvider());

        $this->assertEquals(new ArrayRequestQueue(), $container['queue']);
    }

    public function testGetDoctrineQueue()
    {
        $container = new Container();
        $container['listeners'] = function() {
            return [];
        };
        $container->register(new QueueServiceProvider());

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $container['doctrine'] = function () use ($connection) {
            return $connection;
        };

        $this->assertEquals(new DoctrineRequestQueue($connection), $container['queue']);
    }

    public function testRegistersSetupTeardownListeners() {
        $container = new Container();
        $container['listeners'] = function() {
            return [];
        };
        $container->register(new QueueServiceProvider());

        $queue = $this->prophesize(DoctrineRequestQueue::class);
        $queue->onSetup()->shouldBeCalled();
        $queue->onTeardown()->shouldBeCalled();
        $container['queue'] = $queue->reveal();

        $this->assertTrue(is_callable($container['listeners'][CrawlerEvents::SETUP]['queue.setup'][0]));
        $this->assertTrue(is_callable($container['listeners'][CrawlerEvents::TEARDOWN]['queue.teardown'][0]));
        $setup = $container['listeners'][CrawlerEvents::SETUP]['queue.setup'][0];
        $teardown = $container['listeners'][CrawlerEvents::TEARDOWN]['queue.teardown'][0];
        $setup();
        $teardown();
    }

}
