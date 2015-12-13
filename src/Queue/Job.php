<?php

namespace LastCall\Crawler\Queue;


class Job
{

    const FREE = 0;
    const CLAIMED = 1;
    const COMPLETE = 2;

    /**
     * @var string
     */
    private $identifier;

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

    public function __construct($data, $identifier)
    {
        $this->data = $data;
        $this->identifier = $identifier;
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
}
