<?php


namespace LastCall\Crawler\Reporter;


interface ReporterInterface
{
    /**
     * @param array $stats An array of integers, with the following keys:
     *                     - sent: # of requests that have been sent
     *                     - success: # of requests that have succeeded
     *                     - failure: # of requests that have failed
     *                     - exception: # of requests that generated an
     *                     exception
     *                     - remaining: # of requests left in the queue
     *
     * @return void
     */
    public function report(array $stats);
}