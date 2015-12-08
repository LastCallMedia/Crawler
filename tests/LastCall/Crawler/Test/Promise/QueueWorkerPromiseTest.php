<?php

namespace LastCall\Crawler\Test\Promise;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use LastCall\Crawler\Promise\QueueWorkerPromise;
use LastCall\Crawler\Queue\Driver\ArrayDriver;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Queue\Queue;

class QueueWorkerPromiseTest extends \PHPUnit_Framework_TestCase
{

    public function testDynamicallyAddItems()
    {
        $queue = new Queue(new ArrayDriver(), 'foo');
        $queue->push('foo');
        $queue->push('bar');

        $count = 0;
        $promisor = new QueueWorkerPromise($queue,
          function (Job $job) use ($queue, &$count) {
              return $this->getSelfFulfillingPromise(function () use (
                $job,
                $queue,
                &$count
              ) {
                  $count++;
                  if ($job->getData() == 'bar') {
                      $queue->push('baz');
                  }
              });
          });
        $promisor->promise()->wait();
        $this->assertEquals(3, $count);
    }

    public function testCap()
    {
        $queue = new Queue(new ArrayDriver(), 'foo');
        for ($i = 0; $i < 20; $i++) {
            $queue->push('foo');
        }
        $count = 0;
        $promisor = new QueueWorkerPromise($queue,
          function (Job $job) use (&$count) {
              return $this->getSelfFulfillingPromise(function () use (&$count) {
                  $count++;
              });
          }, 5, 5);
        $promisor->promise()->wait();

        $this->assertEquals(5, $count);
    }

    public function testStaticPromises()
    {
        $queue = new Queue(new ArrayDriver(), 'foo');
        $queue->push('foo');
        $queue->push('bar');

        $count = array('promises' => 0, 'fulfilled' => 0);
        $promisor = new QueueWorkerPromise($queue, function(Job $job) use(&$count) {
            $count['promises']++;
            return (new FulfilledPromise($job->getData()))->then(function() use(&$count) {
                $count['fulfilled']++;
            });
        });

        $promisor->promise()->wait();
        $this->assertEquals(2, $count['promises']);
        $this->assertEquals(2, $count['fulfilled']);
    }

    private function getSelfFulfillingPromise(callable $onComplete = null)
    {
        $p = new Promise(function () use (&$p, $onComplete) {
            if (is_callable($onComplete)) {
                $onComplete($p);
            }
            $p->resolve('foo');
        });

        return $p;
    }
}