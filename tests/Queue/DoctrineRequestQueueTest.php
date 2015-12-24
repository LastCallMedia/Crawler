<?php

namespace LastCall\Crawler\Test\Queue;

use Doctrine\DBAL\DriverManager;
use LastCall\Crawler\Queue\DoctrineRequestQueue;

class DoctrineRequestQueueTest extends \PHPUnit_Framework_TestCase
{
    use QueueTestTrait;

    public function getQueue()
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $queue = new DoctrineRequestQueue($connection);
        $queue->onSetup();

        return $queue;
    }

    protected function getAssert()
    {
        return $this;
    }
}
