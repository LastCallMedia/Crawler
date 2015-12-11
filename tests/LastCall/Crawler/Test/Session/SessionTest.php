<?php

namespace LastCall\Crawler\Test\Session;

use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Psr\Http\Message\UriInterface;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;


class SessionTest extends \PHPUnit_Framework_TestCase {

    private function mockConfig(array $listeners = [], array $subscribers = [], $queue = NULL) {
        if(!$queue) {
            $queue = $this->prophesize(RequestQueueInterface::class);
        }
        $urlHandler = $this->prophesize(URLHandler::class);
        $urlHandler->forUrl(Argument::type(UriInterface::class))->willReturn($urlHandler);

        $configuration = $this->prophesize(ConfigurationInterface::class);
        $configuration->getListeners()->willReturn($listeners);
        $configuration->getSubscribers()->willReturn($subscribers);
        $configuration->getQueue()->willReturn($queue);
        $configuration->getUrlHandler()->willReturn($urlHandler);

        return $configuration;
    }

    public function testSetsUrl() {
        $configuration = $this->mockConfig();
        $configuration->getBaseUrl()->willReturn('http://google.com');
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $session = new Session($configuration->reveal(), $dispatcher->reveal());
        $this->assertEquals('http://google.com', $session->getStartUrl());
        $this->assertEquals('http://google.com/1', $session->getStartUrl('http://google.com/1'));
    }

    public function testAddRequest() {
        $request = new Request('GET', 'http://google.com');
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push($request)->shouldBeCalled();
        $configuration = $this->mockConfig([], [], $queue);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $session = new Session($configuration->reveal(), $dispatcher->reveal());
        $session->addRequest($request);
    }

    public function getIsFinishedTests() {
        return [
            [TRUE, 0],
            [FALSE, 1]
        ];
    }

    /**
     * @dataProvider getIsFinishedTests
     */
    public function testIsFinished($expected, $count) {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->count()->willReturn($count);
        $config = $this->mockConfig([], [], $queue);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $this->assertEquals($expected, $session->isFinished());
    }

    public function testAddsListeners() {
        $cb = function() {};
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $configuration->getListeners()->willReturn([
            'foo' => [[$cb, 10]]
        ]);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->addListener('foo', $cb, 10)->shouldBeCalled();
        $configuration->getSubscribers()->willReturn([]);
        new Session($configuration->reveal(), $dispatcher->reveal());
    }

    public function testAddsSubscribers() {
        $subscriberMock = $this->prophesize(EventSubscriberInterface::class);
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $subscriber = $subscriberMock->reveal();
        $dispatcher->addSubscriber($subscriber)->shouldBeCalled();

        $configuration = $this->prophesize(ConfigurationInterface::class);
        $configuration->getListeners()->willReturn([]);
        $configuration->getSubscribers()->willReturn([$subscriber]);
        new Session($configuration->reveal(), $dispatcher->reveal());
    }


    public function testSetup() {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SETUP)->shouldBeCalledTimes(1);
        $config = $this->prophesize(ConfigurationInterface::class);

        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onSetup();
    }

    public function testTeardown() {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN)->shouldBeCalledTimes(1);
        $config = $this->prophesize(ConfigurationInterface::class);

        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onTeardown();
    }

    public function testOnRequestSending() {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SENDING, Argument::type(CrawlerEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();

        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestSending(new Request('GET', 'http://google.com'));
    }

    public function testOnRequestSuccess() {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SUCCESS, Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestSuccess(new Request('GET', 'http://google.com'), new Response());
    }

    public function testOnRequestFailure() {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::FAILURE, Argument::type(CrawlerResponseEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestFailure(new Request('GET', 'http://google.com'), new Response());
    }

    public function testOnRequestException() {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::EXCEPTION, Argument::type(CrawlerExceptionEvent::class))
            ->shouldBeCalledTimes(1);

        $config = $this->mockConfig();
        $session = new Session($config->reveal(), $dispatcher->reveal());
        $session->onRequestException(
            new Request('GET', 'http://google.com'),
            new \Exception('foo'),
            new Response()
        );
    }
}