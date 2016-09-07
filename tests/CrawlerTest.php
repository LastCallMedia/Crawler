<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Session\SessionInterface;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    protected function mockClient(array $requests)
    {
        $handler = new MockHandler($requests);

        return new Client(['handler' => HandlerStack::create($handler)]);
    }

    public function testRequestsAreBubbledUpFromSession()
    {
        $queue = new ArrayRequestQueue();
        $req = new Request('GET', '1');
        $res = new Response(200);
        $queue->push($req);

        $client = $this->mockClient([new Response(200), new Response(200), new Response(400), new Response(400)]);

        $dispatcher = new EventDispatcher();
        $session = new Session($dispatcher);

        $dispatcher->addListener(CrawlerEvents::SUCCESS, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == '1') {
                $event->addAdditionalRequest(new Request('GET', '2'));

                return;
            }
            throw new \Exception('foo');
        });
        $dispatcher->addListener(CrawlerEvents::FAILURE, function (CrawlerResponseEvent $event) {
            if ($event->getRequest()->getUri() == '3') {
                $event->addAdditionalRequest(new Request('GET', '4'));
            }
        });
        $dispatcher->addListener(CrawlerEvents::EXCEPTION, function (CrawlerExceptionEvent $event) {
            if ($event->getRequest()->getUri() == '2') {
                $event->addAdditionalRequest(new Request('GET', '3'));

                return;
            }
        });

        $crawler = new Crawler($session, $client, $queue);
        $crawler->start(1)->wait();

        $this->assertEquals(4, $queue->count($queue::COMPLETE));
    }

    public function testItemIsCompletedOnSuccess()
    {
        $queue = new ArrayRequestQueue();
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(200);
        $queue->push($req);

        $client = $this->mockClient([$res]);

        $session = $this->prophesize(SessionInterface::class);

        $session->start()->shouldBeCalled();
        $session->onRequestSending($req)->shouldBeCalled();
        $session->onRequestSuccess($req, $res)->shouldBeCalled();
        $session->finish()->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testItemIsCompletedOnFailure()
    {
        $queue = new ArrayRequestQueue();
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(400);
        $queue->push($req);
        $client = $this->mockClient([$res]);

        $session = $this->prophesize(SessionInterface::class);

        $session->start()->shouldBeCalled();
        $session->onRequestSending($req)->shouldBeCalled();
        $session->onRequestFailure($req, $res)->shouldBeCalled();
        $session->finish()->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testExceptionEventIsFiredOnSuccesfulResponseException()
    {
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(200);
        $e = new \Exception('foo');

        $queue = new ArrayRequestQueue();
        $queue->push($req);
        $client = $this->mockClient([$res]);
        $session = $this->prophesize(SessionInterface::class);

        $session->start()->shouldBeCalled();
        $session->onRequestSending($req)->shouldBeCalled();
        $session->onRequestSuccess($req, $res)->willThrow($e);
        $session->finish()->shouldBeCalled();

        $session->onRequestException($req, $e, $res)->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testExceptionEventIsFiredOnFailureResponseException()
    {
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(400);
        $e = new \Exception('foo');

        $client = $this->mockClient([$res]);
        $queue = new ArrayRequestQueue();
        $queue->push($req);

        $session = $this->prophesize(SessionInterface::class);

        $session->start()->shouldBeCalled();
        $session->onRequestSending($req)->shouldBeCalled();
        $session->onRequestFailure($req, $res)->willThrow($e);
        $session->onRequestException($req, $e, $res)->shouldBeCalled();
        $session->finish()->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client, $queue);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testExceptionEventIsFiredOnSendingException()
    {
        $req = new Request('GET', 'https://lastcallmedia.com');
        $res = new Response(400);
        $e = new \Exception('foo');

        $queue = new ArrayRequestQueue();
        $queue->push($req);
        $client = $this->mockClient([$res]);

        $session = $this->prophesize(SessionInterface::class);

        $session->start()->shouldBeCalled();
        $session->onRequestSending($req)->willThrow($e);
        $session->onRequestException($req, $e, null)->shouldBeCalled();
        $session->finish()->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client, $queue);
        $crawler->start(1)->wait();

        // @todo: Should this job be completed?
//        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testQueueIsWorkedUntilEmpty()
    {
        $responses = array_fill(0, 2, new Response(200));
        $count = 0;

        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com/1'));
        $client = $this->mockClient($responses);

        $session = $this->prophesize(SessionInterface::class);

        $session->start()->shouldBeCalled();
        $session->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $session->onRequestSuccess(Argument::type(RequestInterface::class),
            Argument::type(ResponseInterface::class))->will(function ($args) use (
            &$queue,
            &$count
        ) {
            $request = $args[0];
            if ($request->getUri() == 'https://lastcallmedia.com/1') {
                $queue->push(new Request('GET', 'http://google.com/2'));
            }
            ++$count;
        });
        $session->finish()->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client, $queue);
        $crawler->start(5)->wait();

        $this->assertEquals(2, $count);
    }
}
