<?php

namespace LastCall\Crawler\Test\Configuration;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\RequestData\ArrayRequestDataStore;
use LastCall\Crawler\RequestData\DoctrineRequestDataStore;
use LastCall\Crawler\RequestData\RequestDataStore;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContainerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClient()
    {
        $config = new Configuration();
        $this->assertInstanceOf(ClientInterface::class, $config->getClient());
    }

    public function testHasQueue()
    {
        $config = new Configuration();
        $this->assertEquals(new ArrayRequestQueue(), $config->getQueue());
    }

    public function testUsesDoctrineQueue()
    {
        $connection = $this->prophesize(Connection::class)->reveal();
        $config = new Configuration('', [
            'doctrine' => $connection,
        ]);
        $this->assertEquals(new DoctrineRequestQueue($connection), $config->getQueue());
    }

    public function testHasDataStore() {
        $config = new Configuration();
        $this->assertEquals(new ArrayRequestDataStore(), $config->getDataStore());
    }

    public function testUsesDoctrineDataStore() {
        $connection = $this->prophesize(Connection::class)->reveal();
        $config = new Configuration('', [
            'doctrine' => $connection,
        ]);
        $this->assertEquals(new DoctrineRequestDataStore($connection), $config->getDataStore());
    }

    public function testHasLogger()
    {
        $config = new Configuration();
        $this->assertInstanceOf(LoggerInterface::class, $config['logger']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown logger: foo
     */
    public function testThrowsExceptionOnInvalidLogger()
    {
        $config = new Configuration('', ['loggers' => ['foo']]);
        $config->attachToDispatcher($this->prophesize(EventDispatcherInterface::class)->reveal());
    }

    public function testAddsValidLogger()
    {
        $logger = $this->prophesize(EventSubscriberInterface::class);

        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addListener(Argument::type('string'), Argument::any())
            ->will(function () {
            });
        $dispatcher->addSubscriber(Argument::type(EventSubscriberInterface::class))
            ->will(function () {
            });
        $dispatcher->addSubscriber($logger)->shouldBeCalled();

        $config = new Configuration('', [
            'loggers' => ['foo'],
            'logger.foo' => $logger->reveal(),
        ]);

        $config->attachToDispatcher($dispatcher->reveal());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown discoverer: foo
     */
    public function testThrowsExceptionOnInvalidDiscoverer()
    {
        $config = new Configuration('', ['discoverers' => ['foo']]);
        $config->attachToDispatcher($this->prophesize(EventDispatcherInterface::class)->reveal());
    }

    public function testAddsValidDiscoverer()
    {
        $discoverer = $this->prophesize(EventSubscriberInterface::class);

        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addListener(Argument::type('string'), Argument::any())
            ->will(function () {
            });
        $dispatcher->addSubscriber(Argument::type(EventSubscriberInterface::class))
            ->will(function () {
            });
        $dispatcher->addSubscriber($discoverer)->shouldBeCalled();

        $config = new Configuration('', [
            'discoverers' => ['foo'],
            'discoverer.foo' => $discoverer->reveal(),
        ]);

        $config->attachToDispatcher($dispatcher->reveal());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown recursor: foo
     */
    public function testThrowsExceptionOnInvalidRecursor()
    {
        $config = new Configuration('', ['recursors' => ['foo']]);
        $config->attachToDispatcher($this->prophesize(EventDispatcherInterface::class)->reveal());
    }

    public function testAddsValidRecursor()
    {
        $recursor = $this->prophesize(EventSubscriberInterface::class);

        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addListener(Argument::type('string'), Argument::any())
            ->will(function () {
            });
        $dispatcher->addSubscriber(Argument::type(EventSubscriberInterface::class))
            ->will(function () {
            });
        $dispatcher->addSubscriber($recursor)->shouldBeCalled();

        $config = new Configuration('', [
            'recursors' => ['foo'],
            'recursor.foo' => $recursor->reveal(),
        ]);

        $config->attachToDispatcher($dispatcher->reveal());
    }

    public function testAddsInitialRequestOnStart()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $queue = $config->getQueue();
        $this->assertEquals(0, $queue->count());
        $dispatcher = new EventDispatcher();
        $config->attachToDispatcher($dispatcher);

        $event = new CrawlerStartEvent();
        $dispatcher->dispatch(CrawlerEvents::START, $event);
        $this->assertCount(1, $event->getAdditionalRequests());
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

    public function testSetsUpAndTearsDownDataStore() {
        $config = new Configuration();
        $store = $this->prophesize(RequestDataStore::class);
        $store->willImplement(SetupTeardownInterface::class);
        $store->onSetup()->shouldBeCalled();
        $store->onTeardown()->shouldBeCalled();

        $config['datastore'] = $store->reveal();
        $dispatcher = new EventDispatcher();
        $config->attachToDispatcher($dispatcher);
        $dispatcher->dispatch(CrawlerEvents::SETUP);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN);
    }

    public function testSetsOutput()
    {
        $output = $this->prophesize(OutputInterface::class)->reveal();
        $config = new Configuration();
        $config->setOutput($output);
        $this->assertEquals($output, $config['output']);
    }
}
