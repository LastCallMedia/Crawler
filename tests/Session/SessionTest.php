<?php

namespace LastCall\Crawler\Test\Session;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
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
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::START)->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->start();
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
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::EXCEPTION,
            Argument::type(CrawlerExceptionEvent::class))
            ->shouldBeCalledTimes(1);

        $session = new Session($dispatcher->reveal());
        $session->onRequestException(new Request('GET', 'http://google.com'),
            new \Exception('foo'), new Response());
    }
}
