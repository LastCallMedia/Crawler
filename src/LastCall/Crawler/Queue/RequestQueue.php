<?php

namespace LastCall\Crawler\Queue;

use LastCall\Crawler\Queue\Driver\DriverInterface;
use Psr\Http\Message\RequestInterface;

class RequestQueue implements RequestQueueInterface
{

    /**
     * @var \LastCall\Crawler\Queue\Driver\DriverInterface
     */
    private $driver;

    /**
     * @var string
     */
    private $channel;

    public function __construct(DriverInterface $driver, $channel)
    {
        $this->driver = $driver;
        $this->channel = $channel;
    }

    public function push(RequestInterface $request)
    {
        $key = $request->getMethod() . $request->getUri();
        $job = new Job($this->channel, $request, $key);
        return $this->driver->push($job);
    }

    public function pop($leaseTime = 30)
    {
        return $this->driver->pop($this->channel, $leaseTime);
    }

    public function complete(Job $job)
    {
        return $this->driver->complete($job);
    }

    public function release(Job $job)
    {
        return $this->driver->release($job);
    }

    public function count($status = Job::FREE) {
        return $this->driver->count($this->channel, $status);
    }
}