<?php


namespace LastCall\Crawler\Test\Handler\Module\Subscription;


use LastCall\Crawler\Handler\Module\ModuleSubscription;
use LastCall\Crawler\Test\Resources\DummyProcessor;

class ModuleSubscriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscriptionProperties() {
        $processor = new DummyProcessor('xxx', 'xxx');
        $subscription = new ModuleSubscription($processor, 'foo', 'bar', 'baz');
        $this->assertEquals('foo', $subscription->getParserId());
        $this->assertEquals('bar', $subscription->getSelector());
        $this->assertSame($processor, $subscription->getProcessor());
        $this->assertEquals([$processor, 'baz'], $subscription->getCallable());
    }

}