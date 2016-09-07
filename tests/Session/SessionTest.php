<?php

namespace LastCall\Crawler\Test\Session;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Session\Session;
use Prophecy\Argument;
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
        $dispatcher = new EventDispatcher();

        $config = new Configuration('https://lastcallmedia.com');
        $config['queue'] = $queue;
        $config['subscribers'] = [$subscriber];
        $config->addListener(CrawlerEvents::SUCCESS, $listener);

        $session = Session::createFromConfig($config, $dispatcher);
        $listeners = $dispatcher->getListeners(CrawlerEvents::SUCCESS);
        $this->assertSame($subscriber, $listeners[1][0]);
        $this->assertSame($listener, $listeners[0]);
    }

    public function testStart()
    {
        $called = 0;
        $dispatcher = new EventDispatcher();
        $session = new Session($dispatcher);
        $dispatcher->addListener(CrawlerEvents::START, function(CrawlerStartEvent $event) use (&$called, $session) {
            $this->assertSame($session, $event->getSession());
            $called++;
        });
        $session->start();
        $this->assertEquals(1, $called);
    }

    public function testSetup()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SETUP)->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->setup();
    }

    public function testTeardown()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN)->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->teardown();
    }

    public function testFinish()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::FINISH)->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->finish();
    }

    public function testOnRequestSending()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SENDING,
            Argument::type(CrawlerEvent::class))->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->onRequestSending(new Request('GET', 'http://google.com'));
    }

    public function testOnRequestSuccess()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SUCCESS,
            Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->onRequestSuccess(new Request('GET', 'http://google.com'),
            new Response());
    }

    public function testOnRequestFailure()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::FAILURE,
            Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->onRequestFailure(new Request('GET', 'http://google.com'),
            new Response());
    }

    public function testOnRequestException()
    {
        $called = 0;
        $exception = new \Exception('foo');
        $request = new Request('GET', 'http://google.com');
        $response = new Response();
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function(CrawlerExceptionEvent $event) use (&$called, $request, $exception, $response) {
            $this->assertSame($request, $event->getRequest());
            $this->assertSame($response, $event->getResponse());
            $this->assertSame($exception, $event->getException());
            $called++;
        });
        $session = new Session($dispatcher);
        $session->onRequestException($request, $exception, $response);

        $this->assertEquals(1, $called);
    }
}
