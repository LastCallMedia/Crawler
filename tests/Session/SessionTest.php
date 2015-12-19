<?php

namespace LastCall\Crawler\Test\Session;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class SessionTest extends \PHPUnit_Framework_TestCase
{

    private function mockConfig(
        array $listeners = [],
        array $subscribers = [],
        $queue = null
    ) {
        if (!$queue) {
            $queue = $this->prophesize(RequestQueueInterface::class);
        }
        $urlHandler = $this->prophesize(URLHandler::class);
        $urlHandler->forUrl(Argument::type(UriInterface::class))
            ->willReturn($urlHandler);

        $configuration = $this->prophesize(ConfigurationInterface::class);
        $configuration->getListeners()->willReturn($listeners);
        $configuration->getSubscribers()->willReturn($subscribers);
        $configuration->getQueue()->willReturn($queue);
        $configuration->getUrlHandler()->willReturn($urlHandler);

        return $configuration;
    }

    public function testAddRequest()
    {
        $request = new Request('GET', 'http://google.com');
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push($request)->shouldBeCalled();
        $configuration = $this->mockConfig([], [], $queue);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $session = new Session($configuration->reveal(), $dispatcher->reveal());
        $session->addRequest($request);
    }

    public function getIsFinishedTests()
    {
        return [
            [true, 0],
            [false, 1]
        ];
    }

    /**
     * @dataProvider getIsFinishedTests
     */
    public function testIsFinished($expected, $count)
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->count()->willReturn($count);
        $config = $this->mockConfig([], [], $queue);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $this->assertEquals($expected, $session->isFinished());
    }

    public function getInitTests()
    {
        return [
            ['', 'https://lastcallmedia.com'],
            ['https://lastcallmedia.com/1', 'https://lastcallmedia.com/1'],
        ];
    }

    /**
     * @dataProvider getInitTests
     */
    public function testInit($baseUrl, $expectedadd)
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push(Argument::that(function (RequestInterface $request) use (
            $expectedadd
        ) {
            return (string)$request->getUri() === $expectedadd;
        }))->shouldBeCalled();
        $config = $this->mockConfig([], [], $queue);
        $config->getBaseUrl()->willReturn('https://lastcallmedia.com');
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->init($baseUrl);
    }

    public function testAddsListeners()
    {
        $cb = function () {
        };
        $queue = $this->prophesize(RequestQueueInterface::class);
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $configuration->getQueue()->willReturn($queue);
        $configuration->getListeners()->willReturn([
            'foo' => [[$cb, 10]]
        ]);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addListener('foo', $cb, 10)->shouldBeCalled();
        $configuration->getSubscribers()->willReturn([]);
        new Session($configuration->reveal(), $dispatcher->reveal());
    }

    public function testAddsSubscribers()
    {
        $subscriberMock = $this->prophesize(EventSubscriberInterface::class);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $queue = $this->prophesize(RequestQueueInterface::class);

        $subscriber = $subscriberMock->reveal();
        $dispatcher->addSubscriber($subscriber)->shouldBeCalled();

        $configuration = $this->prophesize(ConfigurationInterface::class);
        $configuration->getQueue()->willReturn($queue);
        $configuration->getListeners()->willReturn([]);
        $configuration->getSubscribers()->willReturn([$subscriber]);
        new Session($configuration->reveal(), $dispatcher->reveal());
    }


    public function testSetup()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SETUP)->shouldBeCalledTimes(1);
        $config = $this->prophesize(ConfigurationInterface::class);

        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onSetup();
    }

    public function testTeardown()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN)->shouldBeCalledTimes(1);
        $config = $this->prophesize(ConfigurationInterface::class);

        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onTeardown();
    }

    public function testNext()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = new ArrayRequestQueue();
        $queue->push($request);
        $config = new Configuration('https://lastcallmedia.com');
        $config->setQueue($queue);
        $dispatcher = new EventDispatcher();

        $session = new Session($config, $dispatcher);
        $this->assertSame($request, $session->next());
    }

    public function testComplete()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = new ArrayRequestQueue();
        $queue->push($request);
        $config = new Configuration('https://lastcallmedia.com');
        $config->setQueue($queue);

        $session = new Session($config, new EventDispatcher());
        $popped = $session->next();
        $session->complete($popped);
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testRelease()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = new ArrayRequestQueue();
        $queue->push($request);
        $config = new Configuration('https://lastcallmedia.com');
        $config->setQueue($queue);

        $session = new Session($config, new EventDispatcher());
        $popped = $session->next();
        $session->release($popped);
        $this->assertEquals(1, $queue->count($queue::FREE));
    }

    public function testOnRequestSending()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SENDING,
            Argument::type(CrawlerEvent::class))->shouldBeCalledTimes(1);

        $config = $this->mockConfig();

        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestSending(new Request('GET', 'http://google.com'));
    }

    public function testOnRequestSuccess()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SUCCESS,
            Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestSuccess(new Request('GET', 'http://google.com'),
            new Response());
    }

    public function testOnRequestFailure()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::FAILURE,
            Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestFailure(new Request('GET', 'http://google.com'),
            new Response());
    }

    public function testOnRequestException()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::EXCEPTION,
            Argument::type(CrawlerExceptionEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestException(new Request('GET', 'http://google.com'),
            new \Exception('foo'), new Response());
    }

    public function testAddsAdditionalRequests()
    {
        $newRequest = new Request('GET', 'https://lastcallmedia.com');
        $fn = function (CrawlerEvent $event) use ($newRequest) {
            $event->addAdditionalRequest($newRequest);
        };
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push($newRequest)->shouldBeCalled();
        $config = $this->mockConfig([], [], $queue);
        $config->getListeners()->willReturn([
            CrawlerEvents::SENDING => [[$fn, 0]]
        ]);
        $session = new Session($config->reveal(), new EventDispatcher());
        $session->onRequestSending(new Request('GET', 'http://google.com'));
    }

    public function testSetsUpQueue()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->willImplement(SetupTeardownInterface::class);
        $queue->onSetup()->shouldBeCalled();

        $config = $this->mockConfig([], [], $queue);
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onSetup();
    }

    public function testTearsDownQueue()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->willImplement(SetupTeardownInterface::class);
        $queue->onTeardown()->shouldBeCalled();

        $config = $this->mockConfig([], [], $queue);
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onTeardown();
    }
}