<?php

namespace LastCall\Crawler\Test\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Queue\Job;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StubCrawler extends Crawler
{

    public function __construct(array $responses = [], $dispatcher = null)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $config = new Configuration();
        $dispatcher = $dispatcher ?: new EventDispatcher();
        $config->setDispatcher($dispatcher);
        $config->setClient(new Client(['handler' => $handler]));
        parent::__construct($config);
    }

    private function getQueueDriver()
    {
        $refl = new \ReflectionObject($this->configuration->getRequestQueue());
        $prop = $refl->getProperty('driver');
        $prop->setAccessible(true);

        return $prop->getValue($this->configuration->getRequestQueue());
    }

    public function inspectRequestQueue()
    {
        return $this->getQueueDriver()->inspect('request');
    }

    public function countRequestQueue($status = Job::FREE)
    {
        return $this->getQueueDriver()->count('request', $status);
    }

    public function _getRequestQueue()
    {
        return $this->configuration->getRequestQueue();
    }
}