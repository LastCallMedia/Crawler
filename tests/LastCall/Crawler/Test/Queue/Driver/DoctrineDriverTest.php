<?php

namespace LastCall\Crawler\Test\Queue\Driver;

use Doctrine\DBAL\DriverManager;
use LastCall\Crawler\Queue\Driver\DoctrineDriver;

class DoctrineDriverTest extends \PHPUnit_Framework_TestCase
{

    use DriverTestTrait;
    use UniqueDriverTestTrait;

    public function getDriver()
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => TRUE,
        ]);
        $driver = new DoctrineDriver($connection);
        $driver->createTable();
        return $driver;
    }
}