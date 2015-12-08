<?php

namespace LastCall\Crawler\Queue;

use LastCall\Crawler\Queue\Driver\DriverInterface;
use LastCall\Crawler\Queue\Driver\UniqueJobInterface;

class Queue implements QueueInterface
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

    public function push($data, $key = null)
    {
        $job = new Job($this->channel, $data, $key);
        if ($key) {
            if ($this->driver instanceof UniqueJobInterface) {
                return $this->driver->pushUnique($job, $key);
            }
            throw new \InvalidArgumentException('Driver does not handle unique items.');
        } else {
            return $this->driver->push($job);
        }
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