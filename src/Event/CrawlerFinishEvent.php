<?php

namespace LastCall\Crawler\Event;

use LastCall\Crawler\RequestData\RequestDataStore;
use Symfony\Component\EventDispatcher\Event;

class CrawlerFinishEvent extends Event
{
    private $store;

    public function __construct(RequestDataStore $store)
    {
        $this->store = $store;
    }

    public function getDataStore()
    {
        return $this->store;
    }
}
