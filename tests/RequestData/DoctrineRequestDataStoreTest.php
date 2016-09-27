<?php


namespace LastCall\Crawler\Test\RequestData;


use Doctrine\DBAL\DriverManager;
use LastCall\Crawler\RequestData\DoctrineRequestDataStore;

class DoctrineRequestDataStoreTest extends \PHPUnit_Framework_TestCase {

    use RequestDataStoreTestTrait;

    public function getStore() {
        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $store = new DoctrineRequestDataStore($conn);
        $store->onSetup();

        return $store;
    }

}