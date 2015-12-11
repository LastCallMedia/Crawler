<?php

namespace LastCall\Crawler\Queue\Driver;

use LastCall\Crawler\Queue\Job;

interface DriverInterface
{

    /**
     * @param \LastCall\Crawler\Queue\Job $job
     *
     * @return true|false A boolean indicating whether the job was added or not.
     */
    public function push(Job $job);

    /**
     * @param int $leaseTime
     *
     * @return \LastCall\Crawler\Queue\Job|null
     */
    public function pop($channel, $leaseTime = 30);

    /**
     * @param \LastCall\Crawler\Queue\Job $job
     *
     * @return true|false A boolean indicating whether the job was completed.
     */
    public function complete(Job $job);

    /**
     * @param \LastCall\Crawler\Queue\Job $job
     *
     * @return true|false A boolean indicating whether the job was released.
     */
    public function release(Job $job);

    /**
     * @param string $channel
     * @param int    $status
     *
     * @return int
     */
    public function count($channel, $status = Job::FREE);

}