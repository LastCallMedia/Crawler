<?php

namespace LastCall\Crawler\Queue\Driver;

use LastCall\Crawler\Queue\Job;

class ArrayDriver implements DriverInterface, UniqueJobInterface
{

    private $jobs = array();

    public function pushUnique(Job $job, $key)
    {
        return $this->doPush($job, $key);
    }

    public function doPush(Job $job, $key)
    {
        $channel = $job->getQueue();
        if (!isset($this->jobs[$channel][$key])) {
            $job->setIdentifier($key);
            $this->jobs[$channel][$key] = $job;

            return true;
        }

        return false;
    }

    public function push(Job $job)
    {
        return $this->doPush($job, uniqid());
    }

    public function pop($channel, $leaseTime = 30)
    {
        if (empty($this->jobs[$channel])) {
            return;
        }

        $now = time();
        foreach ($this->jobs[$channel] as $job) {
            if ($job->getStatus() === Job::FREE && $job->getExpire() <= $now) {
                $job->setExpire(time() + $leaseTime);

                return $job;
            }
        }
    }

    public function complete(Job $job)
    {
        $channel = $job->getQueue();
        if (empty($this->jobs[$channel])) {
            throw new \InvalidArgumentException('This job is not managed by this queue driver.');
        }

        if (false !== $idx = array_search($job, $this->jobs[$channel])) {
            $this->jobs[$channel][$idx]->setStatus(Job::COMPLETE)->setExpire(0);

            return true;
        }

        return false;
    }

    public function release(Job $job)
    {
        $channel = $job->getQueue();
        if (empty($this->jobs[$channel])) {
            throw new \InvalidArgumentException('This job is not managed by this queue driver.');
        }

        if (false !== $idx = array_search($job, $this->jobs[$channel])) {
            $this->jobs[$channel][$idx]->setStatus(Job::FREE)->setExpire(0);

            return true;
        }

        return false;
    }

    public function count($channel, $status = Job::FREE)
    {
        if (empty($this->jobs[$channel])) {
            return 0;
        }

        return array_reduce($this->jobs[$channel],
            function ($count, Job $job) use ($status) {
                switch ($status) {
                    case Job::FREE:
                        return $job->getStatus() === Job::FREE && $job->getExpire() <= time() ? $count + 1 : $count;
                    case Job::CLAIMED:
                        return $job->getStatus() === Job::FREE && $job->getExpire() > time() ? $count + 1 : $count;
                    case Job::COMPLETE:
                        return $job->getStatus() === Job::COMPLETE ? $count + 1 : $count;
                }
            }, 0);
    }

    public function inspect($channel)
    {
        if (empty($this->jobs[$channel])) {
            return [];
        }

        return $this->jobs[$channel];
    }

}