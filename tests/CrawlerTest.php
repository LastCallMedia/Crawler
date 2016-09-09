<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerStartEvent;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
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
        $crawler = new Crawler($dispatcher->reveal(), $this->mockClient([]), $queue->reveal());
        $crawler->setup();
    }

    public function testFiresTeardownEvent()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::TEARDOWN)->shouldBeCalled();
        $queue = $this->prophesize(RequestQueueInterface::class);
        $crawler = new Crawler($dispatcher->reveal(), $this->mockClient([]), $queue->reveal());
        $crawler->teardown();
    }

    public function testFiresStartFinish()
    {
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->dispatch(CrawlerEvents::START, Argument::type(CrawlerStartEvent::class))->shouldBeCalled();
        $dispatcher->dispatch(CrawlerEvents::FINISH, Argument::type(Event::class))->shouldBeCalled();
        $queue = $this->prophesize(RequestQueueInterface::class);
        $crawler = new Crawler($dispatcher->reveal(), $this->mockClient([]), $queue->reveal());
        $crawler->start()->wait();
    }

    public function testRequestBubbledFromSuccess()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'success'));
        $client = $this->mockClient([new Response(200), new Response(200)]);
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'success') {
                $event->addAdditionalRequest(new Request('GET', 'request2'));
            }
        });

        $crawler = new Crawler($dispatcher, $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromFailure()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'failure'));
        $client = $this->mockClient([new Response(400), new Response(400)]);
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(CrawlerEvents::FAILURE, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'failure') {
                $event->addAdditionalRequest(new Request('GET', 'failure2'));
            }
        });

        $crawler = new Crawler($dispatcher, $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromSendingException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'sendingexception'));
        $dispatcher = new EventDispatcher();
        $client = $this->mockClient([new Response(200), new Response(200)]);

        $dispatcher->addListener(CrawlerEvents::SENDING, function (CrawlerEvent $event) {
            if ($event->getRequest()->getUri() == 'sendingexception') {
                throw new \Exception('Testing');
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'sendingexception2'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromSuccessException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'successexception'));
        $dispatcher = new EventDispatcher();
        $client = $this->mockClient([new Response(200), new Response(200)]);

        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'successexception') {
                throw new \Exception('Testing');
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'successexception2'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testRequestBubbledFromFailureException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'failureexception'));
        $dispatcher = new EventDispatcher();
        $client = $this->mockClient([new Response(400), new Response(400)]);

        $dispatcher->addListener(CrawlerEvents::FAILURE, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == 'failureexception') {
                throw new \Exception('Testing');
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            $event->addAdditionalRequest(new Request('GET', 'failureexception2'));
        });

        $crawler = new Crawler($dispatcher, $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(2, $queue->count($queue::COMPLETE));
    }

    public function testQueueIsWorkedUntilEmpty()
    {
        $count = 0;

        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', '1'));
        $client = $this->mockClient([new Response(200), new Response(200)]);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) use ($queue, &$count) {
            ++$count;
            if ($event->getRequest()->getUri() == '1') {
                $queue->push(new Request('GET', '2'));
            }
        });

        $crawler = new Crawler($dispatcher, $client, $queue);
        $crawler->start(5)->wait();
        $this->assertEquals(2, $count);
    }
}
