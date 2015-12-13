<?php

namespace LastCall\Crawler\Queue;


use Psr\Http\Message\RequestInterface;

interface RequestQueueInterface
{

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return bool
     */
    public function push(RequestInterface $request);

    /**
     * @return Job|null
     */
    public function pop();

    /**
     * @param \LastCall\Crawler\Queue\Job $job
     *
     * @return bool
     */
    public function complete(Job $job);

    /**
     * @param \LastCall\Crawler\Queue\Job $job
     *
     * @return bool
     */
    public function release(Job $job);

    public function count($status = Job::FREE);
}