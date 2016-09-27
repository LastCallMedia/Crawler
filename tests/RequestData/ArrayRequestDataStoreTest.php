<?php

namespace LastCall\Crawler\Test\RequestData;

use LastCall\Crawler\RequestData\ArrayRequestDataStore;

class ArrayRequestDataStoreTest extends \PHPUnit_Framework_TestCase
{
    use RequestDataStoreTestTrait;

    public function getStore()
    {
        return new ArrayRequestDataStore();
    }
}
