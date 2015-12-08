<?php

namespace LastCall\Crawler\Queue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  uniqueConstraints={@ORM\UniqueConstraint(name="queue_identifier",
 *  columns={"identifier", "queue"})}
 * )
 */
class Job
{

    const FREE = 0;
    const CLAIMED = 1;
    const COMPLETE = 2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="identifier", type="binary", nullable=true)
     */
    private $identifier;

    /**
     * @ORM\Column(type="string")
     */
    private $queue;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::FREE;

    /**
     * @ORM\Column(type="integer")
     */
    private $expire = 0;

    /**
     * @ORM\Column(type="object")
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

    public function setQueue($queue)
    {
        $this->queue = $queue;
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

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
