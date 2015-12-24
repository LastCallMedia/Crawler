<?php

namespace LastCall\Crawler\Test\Fragment;

use LastCall\Crawler\Fragment\FragmentSubscription;
use LastCall\Crawler\Test\Resources\DummyProcessor;

class ModuleSubscriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscriptionProperties()
    {
        $processor = new DummyProcessor('xxx', 'xxx');
        $subscription = new FragmentSubscription($processor, 'foo', 'bar',
            'baz');
        $this->assertEquals('foo', $subscription->getParserId());
        $this->assertEquals('bar', $subscription->getSelector());
        $this->assertSame($processor, $subscription->getProcessor());
        $this->assertEquals([$processor, 'baz'], $subscription->getCallable());
    }
}
