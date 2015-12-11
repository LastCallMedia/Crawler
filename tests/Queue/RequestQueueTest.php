<?php
/**
 * Created by PhpStorm.
 * User: rfbayliss
 * Date: 12/11/15
 * Time: 3:54 PM
 */

namespace LastCall\Crawler\Test\Queue;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Queue\Driver\DriverInterface;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Queue\RequestQueue;
use Prophecy\Argument;

class RequestQueueTest extends \PHPUnit_Framework_TestCase
{
    public function getDriver()
    {

    }

    public function testPush()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $driver->push(Argument::type(Job::class))->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->push(new Request('GET', 'http://google.com'));
    }

    public function testPop()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $driver->pop('request', 10)->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->pop(10);
    }

    public function testComplete()
    {
        $job = $this->prophesize(Job::class)->reveal();
        $driver = $this->prophesize(DriverInterface::class);
        $driver->complete($job)->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->complete($job);
    }

    public function testRelease()
    {
        $job = $this->prophesize(Job::class)->reveal();
        $driver = $this->prophesize(DriverInterface::class);
        $driver->release($job)->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->release($job);
    }

    public function testCount()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $driver->count('request', 1)->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->count(1);
    }

    public function testOnSetupCalledForImplementingQueues()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $driver->willImplement(SetupTeardownInterface::class);
        $driver->onSetup()->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->onSetup();
    }

    public function testOnSetupNotCalledForNormalQueues()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->onSetup();
    }

    public function testOnTeardownCalledForImplementingQueues()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $driver->willImplement(SetupTeardownInterface::class);
        $driver->onTeardown()->shouldBeCalled();
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->onTeardown();
    }

    public function testOnTeardownNotCalledForNormalQueues()
    {
        $driver = $this->prophesize(DriverInterface::class);
        $queue = new RequestQueue($driver->reveal(), 'request');
        $queue->onTeardown();
    }
}