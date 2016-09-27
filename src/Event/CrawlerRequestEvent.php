<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\Common\HasAdditionalRequests;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps data for a request cycle.
 */
class CrawlerRequestEvent extends Event
{
    use HasAdditionalRequests;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    private $data = [];

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get the request that was made.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function addData($key, $value) {
        $this->data[$key] = $value;
    }

    public function getData() {
        return $this->data;
    }
}
