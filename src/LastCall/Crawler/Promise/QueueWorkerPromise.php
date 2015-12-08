<?php

namespace LastCall\Crawler\Promise;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use LastCall\Crawler\Queue\QueueInterface;

class QueueWorkerPromise
{

    /**
     * @var int
     */
    private $concurrency;

    /**
     * @var int
     */
    private $max;

    /**
     * @var int
     */
    private $added = 0;

    /**
     * @var \LastCall\Crawler\Queue\QueueInterface
     */
    private $queue;

    /**
     * @var callable
     */
    private $promisor;

    /**
     * @var
     */
    private $aggregate;

    /**
     * @var array
     */
    private $pending = array();

    public function __construct(
      QueueInterface $queue,
      callable $promisor,
      $concurrency = 5,
      $max = 0
    ) {
        $this->queue = $queue;
        $this->promisor = $promisor;
        $this->concurrency = $concurrency;
        $this->max = $max;
    }

    /**
     * Returns a promise.
     *
     * @return PromiseInterface
     */
    public function promise()
    {

        if (!$this->aggregate) {
            $this->aggregate = new Promise(function () {
                reset($this->pending);
                $this->addPending();

                while ($promise = current($this->pending)) {
                    if($promise->getState() == PromiseInterface::PENDING) {
                        $promise->wait();
                    }
                    else {
                        next($this->pending);
                        $this->clearPending();
                        $this->addPending();
                    }
                }
                $this->aggregate->resolve(true);
            });
        }

        return $this->aggregate;
    }

    private function addPending()
    {
        while (count($this->pending) < $this->concurrency && ($this->max === 0 || $this->added < $this->max) && $job = $this->queue->pop()) {
            $promisor = $this->promisor;
            $promise = $promisor($job);
            $this->pending[] = $promise;
            $this->added++;
            $promise->then(function ($val) use ($job) {
                $this->queue->complete($job);
                $this->clearPending();
                $this->addPending();

                return $val;
            }, function ($reason) use ($job) {
                $this->queue->complete($job);
                $this->clearPending();
                $this->addPending();

                return \GuzzleHttp\Promise\rejection_for($reason);
            });
        }
    }

    private function clearPending()
    {
        foreach ($this->pending as $i => $promise) {
            if ($promise->getState() !== Promise::PENDING) {
                unset($this->pending[$i]);
            }
        }
    }
}