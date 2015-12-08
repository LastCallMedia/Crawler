<?php

namespace LastCall\Crawler\Test\Queue\Driver;

use LastCall\Crawler\Queue\Driver\ArrayDriver;

class ArrayDriverTest extends \PHPUnit_Framework_TestCase
{

    use DriverTestTrait;
    use UniqueDriverTestTrait;

    public function getDriver()
    {
        return new ArrayDriver();
    }
}