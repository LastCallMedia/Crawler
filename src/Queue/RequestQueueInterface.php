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
     *
     * @throws \RuntimeException When the request is not in a pending state.
     */
    public function complete(RequestInterface $request);

    /**
     * @param RequestInterface
     *
     * @return bool
     *
     * @throws \RuntimeException When the request is not in a pending state.
     */
    public function release(RequestInterface $request);

    /**
     * Count the number of requests in the queue.
     *
     * @param int $status
     *
     * @return int
     *
     * @throws \RuntimeException When status is not one of the accepted statuses.
     */
    public function count($status = self::FREE);
}