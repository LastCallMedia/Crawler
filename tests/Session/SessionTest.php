<?php

namespace LastCall\Crawler\Test\Session;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class SessionTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateFromConfig()
    {
        $subscriber = new RequestLogger(new NullLogger());
        $listener = function () {
        };
        $queue = new ArrayRequestQueue();
        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $dispatcher = new EventDispatcher();

        $config = new Configuration('https://lastcallmedia.com');
        $config->addSubscriber($subscriber);
        $config->addListener(CrawlerEvents::SUCCESS, $listener);
        $config->setQueue($queue);
        $config->setUrlHandler($urlHandler);


        $session = Session::createFromConfig($config, $dispatcher);
        $listeners = $dispatcher->getListeners(CrawlerEvents::SUCCESS);
        $this->assertSame($subscriber, $listeners[1][0]);
        $this->assertSame($listener, $listeners[0]);
        $queueRefl = new \ReflectionProperty(Session::class, 'queue');
        $queueRefl->setAccessible(true);
        $this->assertSame($queue, $queueRefl->getValue($session));
        $urlHandlerRefl = new \ReflectionProperty(Session::class, 'urlHandler');
        $urlHandlerRefl->setAccessible(true);
        $this->assertSame($urlHandler, $urlHandlerRefl->getValue($session));
    }

    public function testAddRequest()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = $this->prophesize(RequestQueueInterface::class);
        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $queue->push($request)->shouldBeCalled();
        $session = new Session($urlHandler, $queue->reveal());
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
        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, $queue->reveal());
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
        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, $queue->reveal());
        $session->init($baseUrl);
    }

    public function testSetup()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SETUP)->shouldBeCalledTimes(1);
        $urlHandler = new URLHandler('https://lastcallmedia.com');

        $session = new Session($urlHandler, null, $dispatcher->reveal());
        $session->onSetup();
    }

    public function testTeardown()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN)->shouldBeCalledTimes(1);
        $urlHandler = new URLHandler('https://lastcallmedia.com');

        $session = new Session($urlHandler, null, $dispatcher->reveal());
        $session->onTeardown();
    }

    public function testNext()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = new ArrayRequestQueue();
        $queue->push($request);
        $urlHandler = new URLHandler('https://lastcallmedia.com');

        $session = new Session($urlHandler, $queue);
        $this->assertSame($request, $session->next());
    }

    public function testComplete()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = new ArrayRequestQueue();
        $queue->push($request);

        $urlHandler = new URLHandler('https://lastcallmedia.com');

        $session = new Session($urlHandler, $queue);
        $popped = $session->next();
        $session->complete($popped);
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testRelease()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $queue = new ArrayRequestQueue();
        $queue->push($request);
        $urlHandler = new URLHandler('https://lastcallmedia.com');

        $session = new Session($urlHandler, $queue);
        $popped = $session->next();
        $session->release($popped);
        $this->assertEquals(1, $queue->count($queue::FREE));
    }

    public function testOnRequestSending()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SENDING,
            Argument::type(CrawlerEvent::class))->shouldBeCalledTimes(1);

        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, null, $dispatcher->reveal());
        $session->onRequestSending(new Request('GET', 'http://google.com'));
    }

    public function testOnRequestSuccess()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SUCCESS,
            Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, null, $dispatcher->reveal());
        $session->onRequestSuccess(new Request('GET', 'http://google.com'),
            new Response());
    }

    public function testOnRequestFailure()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::FAILURE,
            Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, null, $dispatcher->reveal());
        $session->onRequestFailure(new Request('GET', 'http://google.com'),
            new Response());
    }

    public function testOnRequestException()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::EXCEPTION,
            Argument::type(CrawlerExceptionEvent::class))
            ->shouldBeCalledTimes(1);

        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, null, $dispatcher->reveal());
        $session->onRequestException(new Request('GET', 'http://google.com'),
            new \Exception('foo'), new Response());
    }

    public function testAddsAdditionalRequests()
    {
        $newRequest = new Request('GET', 'https://lastcallmedia.com');
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(CrawlerEvents::SENDING,
            function (CrawlerEvent $event) use ($newRequest) {
                $event->addAdditionalRequest($newRequest);
            });
        $urlHandler = new URLHandler('https://lastcallmedia.com');

        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push($newRequest)->shouldBeCalled();

        $session = new Session($urlHandler, $queue->reveal(), $dispatcher);
        $session->onRequestSending(new Request('GET', 'http://google.com'));
    }

    public function testSetsUpQueue()
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->willImplement(SetupTeardownInterface::class);
        $queue->onSetup()->shouldBeCalled();

        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, $queue->reveal());
        $session->onSetup();
    }

    public function testTearsDownQueue()
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->willImplement(SetupTeardownInterface::class);
        $queue->onTeardown()->shouldBeCalled();

        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $session = new Session($urlHandler, $queue->reveal());
        $session->onTeardown();
    }
}