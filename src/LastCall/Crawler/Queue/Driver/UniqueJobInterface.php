<?php

namespace LastCall\Crawler\Queue\Driver;

use LastCall\Crawler\Queue\Job;

interface UniqueJobInterface extends DriverInterface
{

    public function pushUnique(Job $job, $key);
}