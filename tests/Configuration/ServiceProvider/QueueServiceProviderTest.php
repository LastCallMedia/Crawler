<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use Doctrine\DBAL\DriverManager;
use LastCall\Crawler\Configuration\ServiceProvider\QueueServiceProvider;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use Pimple\Container;

class QueueServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultQueue()
    {
        $container = new Container();
        $container['listeners'] = function () {
            return [];
        };
        $container->register(new QueueServiceProvider());

        $this->assertEquals(new ArrayRequestQueue(), $container['queue']);
    }

    public function testGetDoctrineQueue()
    {
        $container = new Container();
        $container['listeners'] = function () {
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
}
