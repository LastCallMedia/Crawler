<?php


namespace LastCall\Crawler\Test\Queue;


use GuzzleHttp\Psr7\Request;
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

    private function getRequest($suffix = '')
    {
        return new Request('GET', 'https://lastcallmedia.com' . $suffix);
    }

    public function testPush()
    {
        $assert = $this->getAssert();
        $request = $this->getRequest();
        $queue = $this->getQueue();
        $assert->assertTrue($queue->push($request));
        $assert->assertFalse($queue->push($request));
    }

    public function testPop()
    {
        $queue = $this->getQueue();
        $assert = $this->getAssert();
        $pushedRequest = $this->getRequest();
        $queue->push($pushedRequest);
        $poppedRequest = $queue->pop();
        $assert->assertEquals($pushedRequest, $poppedRequest);
        $assert->assertEquals(1, $queue->count($queue::PENDING));
        $assert->assertEquals(0, $queue->count());
        $assert->assertNull($queue->pop());
    }

    public function testPopExpires()
    {
        $queue = $this->getQueue();
        $assert = $this->getAssert();
        $queue->push($this->getRequest());
        $assert->assertInstanceOf(Request::class, $queue->pop(0));
        $assert->assertEquals(1, $queue->count($queue::FREE));
        $assert->assertInstanceOf(Request::class, $queue->pop());
    }

    public function testComplete()
    {
        $assert = $this->getAssert();
        $queue = $this->getQueue();
        $queue->push($this->getRequest());
        $request = $queue->pop();
        $queue->complete($request);
        $assert->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testRelease()
    {
        $assert = $this->getAssert();
        $queue = $this->getQueue();
        $queue->push($this->getRequest());
        $job = $queue->pop();
        $queue->release($job);
        $assert->assertEquals(1, $queue->count($queue::FREE));
    }

    public function testCount()
    {
        $assert = $this->getAssert();
        $queue = $this->getQueue();
        $queue->push($this->getRequest());
        $queue->push($this->getRequest(1));
        $queue->push($this->getRequest(2));

        $queue->complete($queue->pop());
        $queue->pop();
        $assert->assertEquals(1, $queue->count($queue::FREE));
        $assert->assertEquals(1, $queue->count($queue::PENDING));
        $assert->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unexpected status 15
     */
    public function testCountInvalidStatus()
    {
        $queue = $this->getQueue();
        $queue->count(15);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This request is not managed by this queue
     */
    public function testCompleteJobNotOnQueue()
    {
        $queue = $this->getQueue();
        $queue->complete(new Request('GET', 'foo'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This request is not managed by this queue
     */
    public function testReleaseJobNotOnQueue()
    {
        $queue = $this->getQueue();
        $queue->release(new Request('GET', 'foo'));
    }

}