<?php

namespace LastCall\Crawler\Test\Queue\Driver;

use LastCall\Crawler\Queue\Driver\DriverInterface;
use LastCall\Crawler\Queue\Job;
use PHPUnit_Framework_Assert as Assert;

trait DriverTestTrait
{

    /**
     * @return DriverInterface
     */
    abstract public function getDriver();


    public function testPush()
    {
        $driver = $this->getDriver();
        $job = new Job('foo', 'bar');
        Assert::assertTrue($driver->push($job));
        Assert::assertSame(1, $driver->count('foo'));
    }

    public function testPop()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        $job = $driver->pop('foo');
        Assert::assertInstanceOf('LastCall\Crawler\Queue\Job', $job);
        Assert::assertNull($driver->pop('foo'));
        Assert::assertSame('foo', $job->getQueue());
        Assert::assertSame('bar', $job->getData());
        Assert::assertTrue($job->getExpire() > time() + 28);
        Assert::assertSame(1, $driver->count('foo', Job::CLAIMED));
        Assert::assertEmpty(0, $driver->count('foo', Job::COMPLETE));
    }

    public function testPopLimitsByQueue()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        Assert::assertNull($driver->pop('bar'));
    }

    public function testCountLimitsByQueue()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        Assert::assertSame(1, $driver->count('foo'));
        Assert::assertSame(0, $driver->count('bar'));
    }

    public function testRelease()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        $job = $driver->pop('foo');
        Assert::assertTrue($driver->release($job));
        Assert::assertSame(1, $driver->count('foo', Job::FREE));
        Assert::assertSame(0, $driver->count('foo', Job::COMPLETE));
    }

    public function testAutoRelease()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        $driver->pop('foo', 0);
        Assert::assertInstanceOf('LastCall\Crawler\Queue\Job', $driver->pop('foo'));
    }

    public function testReleaseItemForcesStatus()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        $job = $driver->pop('foo');
        $job->setStatus(Job::COMPLETE);
        $driver->release($job);
        Assert::assertSame(Job::FREE, $job->getStatus());
    }

    public function testComplete()
    {
        $driver = $this->getDriver();
        $driver->push(new Job('foo', 'bar'));
        $job = $driver->pop('foo');
        $driver->complete($job);
        Assert::assertSame(Job::COMPLETE, $job->getStatus());
        Assert::assertSame(0, $job->getExpire());
        Assert::assertSame(0, $driver->count('foo', Job::FREE));
        Assert::assertSame(1, $driver->count('foo', Job::COMPLETE));
    }
}