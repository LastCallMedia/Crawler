<?php


namespace LastCall\Crawler\Queue;


use Psr\Http\Message\RequestInterface;

class ArrayRequestQueue implements RequestQueueInterface
{

    private $incomplete = [];
    private $pending = [];
    private $complete = [];
    private $expires = [];

    public function push(RequestInterface $request)
    {
        $key = $this->getKey($request);
        if (!isset($this->incomplete[$key]) && !isset($this->pending[$key]) && !isset($this->complete[$key])) {
            $this->incomplete[$key] = $request;

            return true;
        }

        return false;
    }

    private function getKey(RequestInterface $request)
    {
        return $request->getMethod() . $request->getUri();
    }

    public function pop($leaseTime = 30)
    {
        $this->expire();
        if (!empty($this->incomplete)) {
            $request = array_shift($this->incomplete);
            $key = $this->getKey($request);
            $this->expires[$key] = time() + $leaseTime;

            return $this->pending[$key] = $request;
        }

        return null;
    }

    public function complete(RequestInterface $request)
    {
        $this->expire();
        $key = $this->getKey($request);
        if (isset($this->pending[$key])) {
            $this->complete[$key] = $this->pending[$key];
            unset($this->pending[$key], $this->expires[$key]);

            return;
        }
        throw new \RuntimeException('This job is not managed by this queue');
    }

    public function release(RequestInterface $request)
    {
        $this->expire();
        $key = $this->getKey($request);
        if (isset($this->pending[$key])) {
            $this->incomplete[$key] = $this->pending[$key];
            unset($this->pending[$key], $this->expires[$key]);

            return;
        }
        throw new \RuntimeException('This job is not managed by this queue');
    }

    public function count($status = self::FREE)
    {
        $this->expire();
        switch ($status) {
            case self::FREE:
                return count($this->incomplete);
            case self::PENDING:
                return count($this->pending);
            case self::COMPLETE:
                return count($this->complete);
        }
    }

    private function expire() {
        $time = time();
        $expiring = array_filter($this->expires, function($expiration) use ($time) {
            return $expiration <= $time;
        });
        foreach($expiring as $key => $expiration) {
            $this->incomplete[$key] = $this->pending[$key];
            unset($this->pending[$key], $this->expires[$key]);
        }
    }

}