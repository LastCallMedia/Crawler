<?php

namespace LastCall\Crawler\Queue;


class Job
{

    const FREE = 0;
    const CLAIMED = 1;
    const COMPLETE = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var int
     */
    private $status = self::FREE;

    /**
     * @var int
     */
    private $expire = 0;

    /**
     * @var object
     */
    private $data;

    public function __construct($queue, $data, $identifier = null)
    {
        $this->queue = $queue;
        $this->data = $data;
        $this->identifier = $identifier;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getExpire()
    {
        return $this->expire;
    }

    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }
}
