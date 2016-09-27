<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerFinishEvent;
use LastCall\Crawler\Event\CrawlerRequestEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\RequestData\RequestDataStore;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    protected function mockClient(array $requests)
    {
        $handler = new MockHandler($requests);

        return new Client(['handler' => HandlerStack::create($handler)]);
    }

    public function testFiresSetupEvent()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::SETUP)->shouldBeCalled();
        $queue = $this->prophesize(RequestQueueInterface::class);
        $store = $this->prophesize(RequestDataStore::class);
        $crawler = new Crawler($dispatcher->reveal(), $this->mockClient([]), $queue->reveal(), $store->reveal());
        $crawler->setup();
    }

    public function testFiresTeardownEvent()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN)->shouldBeCalled();
        $queue = $this->prophesize(RequestQueueInterface::class);
        $store = $this->prophesize(RequestDataStore::class);
        $crawler = new Crawler($dispatcher->reveal(), $this->mockClient([]), $queue->reveal(), $store->reveal());
        $crawler->teardown();
    }

    public function testFiresStartFinish()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::START, Argument::type(CrawlerStartEvent::class))->shouldBeCalled();
        $dispatcher->dispatch(CrawlerEvents::FINISH, Argument::type(Event::class))->shouldBeCalled();
        $queue = $this->prophesize(RequestQueueInterface::class);
        $store = $this->prophesize(RequestDataStore::class);
        $crawler = new Crawler($dispatcher->reveal(), $this->mockClient([]), $queue->reveal(), $store->reveal());
        $crawler->start()->wait();
    }

    public function testRequestBubbledFromStart()
    {
        $queue = new ArrayRequestQueue();
        $client = $this->mockClient([new Response(200)]);
        $dispatcher = new EventDispatcher();
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::START, function (CrawlerStartEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'start'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start()->wait();

        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromSuccess()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'success'));
        $client = $this->mockClient([new Response(200), new Response(200)]);
        $dispatcher = new EventDispatcher();
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'success') {
                $event->addAdditionalRequest(new Request('GET', 'request2'));
            }
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(1)->wait();
        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromFailure()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'failure'));
        $client = $this->mockClient([new Response(400), new Response(400)]);
        $dispatcher = new EventDispatcher();
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::FAILURE, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'failure') {
                $event->addAdditionalRequest(new Request('GET', 'failure2'));
            }
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(1)->wait();

        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromSendingException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'sendingexception'));
        $dispatcher = new EventDispatcher();
        $client = $this->mockClient([new Response(200), new Response(200)]);
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::SENDING, function (CrawlerRequestEvent $event) {
            if ($event->getRequest()->getUri() == 'sendingexception') {
                throw new \Exception('Testing');
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'sendingexception2'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(1)->wait();

        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromSuccessException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'successexception'));
        $dispatcher = new EventDispatcher();
        $client = $this->mockClient([new Response(200), new Response(200)]);
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'successexception') {
                throw new \Exception('Testing');
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'successexception2'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(1)->wait();

        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromFailureException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'failureexception'));
        $dispatcher = new EventDispatcher();
        $client = $this->mockClient([new Response(400), new Response(400)]);
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::FAILURE, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'failureexception') {
                throw new \Exception('Testing');
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'failureexception2'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(1)->wait();

        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testDataLogged()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'success'));
        $queue->push(new Request('GET', 'failure'));
        $client = $this->mockClient([new Response(200), new Response(400)]);
        $dispatcher = new EventDispatcher();
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher->addListener(CrawlerEvents::SENDING, function (CrawlerRequestEvent $event) {
            $event->addData('sent', 1);
        });
        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerRequestEvent $event) {
            $event->addData('success', 1);
        });
        $dispatcher->addListener(CrawlerEvents::FAILURE, function (CrawlerRequestEvent $event) {
            $event->addData('failure', 1);
        });

        $store->merge('success', ['sent' => 1])->shouldBeCalledTimes(1);
        $store->merge('success', ['success' => 1])->shouldBeCalledTimes(1);
        $store->merge('failure', ['sent' => 1])->shouldBeCalledTimes(1);
        $store->merge('failure', ['failure' => 1])->shouldBeCalledTimes(1);

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(1)->wait();
    }

    public function testCallsCrawlerFinish()
    {
        $queue = new ArrayRequestQueue();
        $client = $this->mockClient([new Response(200), new Response(400)]);
        $dispatcher = new EventDispatcher();
        $store = $this->prophesize(RequestDataStore::class)->reveal();

        $called = false;
        $dispatcher->addListener(CrawlerEvents::FINISH, function (CrawlerFinishEvent $event) use ($store, &$called) {
            $this->assertEquals($store, $event->getDataStore());
            $called = true;
        });
        $crawler = new Crawler($dispatcher, $client, $queue, $store);
        $crawler->start()->wait();
        $this->assertTrue($called);
    }

    public function testQueueIsWorkedUntilEmpty()
    {
        $count = 0;

        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', '1'));
        $client = $this->mockClient([new Response(200), new Response(200)]);
        $store = $this->prophesize(RequestDataStore::class);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) use ($queue, &$count) {
            ++$count;
            if ($event->getRequest()->getUri() == '1') {
                $queue->push(new Request('GET', '2'));
            }
        });

        $crawler = new Crawler($dispatcher, $client, $queue, $store->reveal());
        $crawler->start(5)->wait();

        $this->assertEquals(0, $queue->count($queue::FREE));
        $this->assertEquals(0, $queue->count($queue::PENDING));
        $this->assertEquals(2, $count);
    }
}
