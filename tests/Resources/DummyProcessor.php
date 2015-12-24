<?php

namespace LastCall\Crawler\Test\Resources;

use LastCall\Crawler\Fragment\FragmentSubscription;
use LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface;

class DummyProcessor implements FragmentProcessorInterface
{
    private $subscriptions = [];

    private $calls;

    public function __construct($parserId, $selector)
    {
        $this->subscriptions[] = new FragmentSubscription($this, $parserId,
            $selector, 'dummyMethod');
    }

    public function getSubscribedMethods()
    {
        return $this->subscriptions;
    }

    public function dummyMethod()
    {
        $this->calls[] = func_get_args();
    }

    public function getCalls()
    {
        return $this->calls;
    }
}
