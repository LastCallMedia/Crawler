<?php

namespace LastCall\Crawler\Queue;


use Psr\Http\Message\RequestInterface;

interface RequestQueueInterface
{
    const FREE = 1;
    const PENDING = 2;
    const COMPLETE = 3;

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return bool
     */
    public function push(RequestInterface $request);

    /**
     * @return RequestInterface|null
     */
    public function pop($leaseTime = 30);

    /**
     * @param RequestInterface
     *
     * @return bool
     */
    public function complete(RequestInterface $request);

    /**
     * @param RequestInterface
     *
     * @return bool
     */
    public function release(RequestInterface $request);

    /**
     * Count the number of requests in the queue.
     *
     * @param int $status
     *
     * @return int
     */
    public function count($status = self::FREE);
}