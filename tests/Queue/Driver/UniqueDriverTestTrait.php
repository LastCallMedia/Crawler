<?php

namespace LastCall\Crawler\Test\Queue\Driver;

use LastCall\Crawler\Queue\Driver\DriverInterface;
use LastCall\Crawler\Queue\Job;
use PHPUnit_Framework_Assert as Assert;

trait UniqueDriverTestTrait
{

    /**
     * @return DriverInterface
     */
    abstract public function getDriver();

    public function testUniqueItemIsRevoked()
    {
        $driver = $this->getDriver();
        Assert::assertTrue($driver->pushUnique(new Job('a', 'b'), 'foo'));
        Assert::assertFalse($driver->pushUnique(new Job('a', 'b'), 'foo'));
        Assert::assertEquals(1, $driver->count('a'));
        $job = $driver->pop('a');
        Assert::assertEquals('b', $job->getData());
    }

    public function testUniqueItemSetsIdentifier() {
        $driver = $this->getDriver();
        $driver->pushUnique(new Job('a', 'b'), 'foo');
        $job = $driver->pop('a');
        Assert::assertSame('foo', $job->getIdentifier());
    }


}