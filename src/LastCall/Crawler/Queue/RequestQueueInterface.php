<?php

namespace LastCall\Crawler\Queue;


use Psr\Http\Message\RequestInterface;

interface RequestQueueInterface
{

    public function push(RequestInterface $request);

    public function pop();

    public function complete(Job $job);

    public function release(Job $job);

    public function count($status = Job::FREE);
}