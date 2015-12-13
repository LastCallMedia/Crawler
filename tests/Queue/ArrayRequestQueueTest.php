<?php


namespace LastCall\Crawler\Test\Queue;


use LastCall\Crawler\Queue\ArrayRequestQueue;

class ArrayRequestQueueTest extends \PHPUnit_Framework_TestCase
{
    use QueueTestTrait;

    protected function getQueue() {
        return new ArrayRequestQueue();
    }

    protected function getAssert() {
        return $this;
    }

}