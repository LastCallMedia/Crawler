<?php


namespace LastCall\Crawler\Test\Queue;


use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Queue\RequestQueueInterface;

trait QueueTestTrait
{
    /**
     * @return RequestQueueInterface
     */
    abstract protected function getQueue();

    /**
     * @return \PHPUnit_Framework_Assert
     */
    abstract protected function getAssert();

    private function getRequest($suffix = '') {
        return new Request('GET', 'https://lastcallmedia.com' . $suffix);
    }
    public function testPush() {
        $assert = $this->getAssert();
        $request = $this->getRequest();
        $queue = $this->getQueue();
        $assert->assertTrue($queue->push($request));
        $assert->assertFalse($queue->push($request));
    }

    public function testPop() {
        $queue = $this->getQueue();
        $assert = $this->getAssert();
        $queue->push($this->getRequest());
        $job = $queue->pop();
        $assert->assertInstanceOf(Job::class, $job);
        $assert->assertEquals('GEThttps://lastcallmedia.com', $job->getIdentifier());
        $assert->greaterThan(time(), $job->getExpire());
        $assert->assertNull($queue->pop());
    }

    public function testComplete() {
        $assert = $this->getAssert();
        $queue = $this->getQueue();
        $queue->push($this->getRequest());
        $job = $queue->pop();
        $assert->assertTrue($queue->complete($job));
        $assert->equalTo(Job::COMPLETE, $job->getStatus());
        $assert->equalTo(0, $job->getExpire());
    }

    public function testRelease() {
        $assert = $this->getAssert();
        $queue = $this->getQueue();
        $queue->push($this->getRequest());
        $job = $queue->pop();
        $assert->assertTrue($queue->release($job));
        $assert->equalTo(Job::FREE, $job->getStatus());
        $assert->equalTo(0, $job->getExpire());
        $assert->assertFalse($queue->release($job));
    }

    public function testCount() {
        $assert = $this->getAssert();
        $queue = $this->getQueue();
        $queue->push($this->getRequest());
        $queue->push($this->getRequest(1));
        $queue->push($this->getRequest(2));

        $queue->complete($queue->pop());
        $queue->pop();
        $assert->assertEquals(1, $queue->count(Job::FREE));
        $assert->assertEquals(1, $queue->count(Job::CLAIMED));
        $assert->assertEquals(1, $queue->count(Job::COMPLETE));
    }

}