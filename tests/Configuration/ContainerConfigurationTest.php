<?php

namespace LastCall\Crawler\Test\Configuration;

use GuzzleHttp\ClientInterface;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContainerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClient()
    {
        $config = new Configuration();
        $this->assertInstanceOf(ClientInterface::class, $config->getClient());
    }

    public function testHasListenersArray()
    {
        $config = new Configuration();
        $this->assertTrue(is_array($config['listeners']));
    }

    public function testHasSubscribersArray()
    {
        $config = new Configuration();
        $this->assertTrue(is_array($config['subscribers']));
    }

    public function testAddListener()
    {
        $fn = function () {
        };
        $config = new Configuration();
        $config->addListener('foo.bar', $fn, 5);
        $this->assertEquals([[$fn, 5]], $config['listeners']['foo.bar']);
    }

    public function testAddSubscriber()
    {
        $subscriber = $this->prophesize(EventSubscriberInterface::class)->reveal();
        $config = new Configuration();
        $config->addSubscriber($subscriber);
        $this->assertTrue(in_array($subscriber, $config['subscribers']));
    }

    public function testAddsInitialRequestOnStart()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $queue = $config->getQueue();
        $this->assertEquals(0, $queue->count());
        $dispatcher = new EventDispatcher();
        $config->attachToDispatcher($dispatcher);
        $dispatcher->dispatch(CrawlerEvents::START, new CrawlerStartEvent());
        $this->assertEquals(1, $queue->count());
    }

    public function testSetsUpAndTearsDownQueue()
    {
        $config = new Configuration();
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->willImplement(SetupTeardownInterface::class);
        $queue->onSetup()->shouldBeCalled();
        $queue->onTeardown()->shouldBeCalled();

        $config['queue'] = $queue->reveal();
        $dispatcher = new EventDispatcher();
        $config->attachToDispatcher($dispatcher);
        $dispatcher->dispatch(CrawlerEvents::SETUP);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }
}
