<?php

namespace LastCall\Crawler\Queue;

use Psr\Http\Message\RequestInterface;

/**
 * Defines an object that accepts requests and tracks completion of those
 * requests.
 */
interface RequestQueueInterface
{
    /**
     * A request that is ready to be sent.
     */
    const FREE = 1;

    /**
     * A request that is pending completion.
     */
    const PENDING = 2;

    /**
     * A request that has been sent.
     */
    const COMPLETE = 3;

    /**
     * Add a request to the queue.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return bool
     */
    public function push(RequestInterface $request);

    /**
     * Adds multiple requests to the queue.
     *
     * @param \Psr\Http\Message\RequestInterface[] $requests
     *
     * @return array
     */
    public function pushMultiple(array $requests);

    /**
     * Retrieve the next request to be processed.
     *
     * @param int $leaseTime The amount of time to hold the request in pending
     *                       state
     *
     * @return \Psr\Http\Message\RequestInterface|null
     */
    public function pop($leaseTime = 30);

    /**
     * Mark a request as complete and remove it from further processing.
     *
     * @param \Psr\Http\Message\RequestInterface
     *
     * @return bool
     *
     * @throws \RuntimeException When the request is not in a pending state
     */
    public function complete(RequestInterface $request);

    /**
     * Mark a request as incomplete and allow it to be processed again.
     *
     * @param \Psr\Http\Message\RequestInterface
     *
     * @return bool
     *
     * @throws \RuntimeException When the request is not in a pending state
     */
    public function release(RequestInterface $request);

    /**
     * Count the number of requests in the queue.
     *
     * @param int $status
     *
     * @return int
     *
     * @throws \RuntimeException When status is not one of the accepted
     *                           statuses
     */
    public function count($status = self::FREE);
}
