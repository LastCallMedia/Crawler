<?php


namespace LastCall\Crawler\Test\Resources;


use LastCall\Crawler\Module\ModuleSubscription;
use LastCall\Crawler\Module\Processor\ModuleProcessorInterface;

class DummyProcessor implements ModuleProcessorInterface
{
    private $subscriptions = [];

    private $calls;

    public function __construct($parserId, $selector)
    {
        $this->subscriptions[] = new ModuleSubscription($this, $parserId, $selector, 'dummyMethod');
    }

    public function getSubscribedMethods()
    {
        return $this->subscriptions;
    }

    public function dummyMethod() {
        $this->calls[] = func_get_args();
    }

    public function getCalls() {
        return $this->calls;
    }
}