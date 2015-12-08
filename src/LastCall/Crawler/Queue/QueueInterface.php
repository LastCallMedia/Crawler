<?php

namespace LastCall\Crawler\Queue;


interface QueueInterface
{

    public function push($data);

    public function pop();

    public function complete(Job $job);

    public function release(Job $job);

    public function count($status = Job::FREE);
}