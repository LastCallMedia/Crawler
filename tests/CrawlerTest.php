<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Session\SessionInterface;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    protected function mockClient(array $requests)
    {
        $handler = new MockHandler($requests);

        return new Client(['handler' => HandlerStack::create($handler)]);
    }

    protected function getMockSession($queue = null)
    {
        if (!$queue) {
            $queue = new ArrayRequestQueue();
        }

        $session = $this->prophesize(SessionInterface::class);
        $session->isFinished()->will(function () use ($queue) {
            return $queue->count() === 0;
        });
        $session->next()->will(function () use ($queue) {
            return $queue->pop();
        });
        $session->complete(Argument::type(RequestInterface::class))
            ->will(function ($args) use ($queue) {
                return $queue->complete($args[0]);
            });
        $session->start()->shouldBeCalled();

        return $session;
    }

    public function testItemIsCompletedOnSuccess()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com'));

        $client = $this->mockClient([new Response(200)]);

        $session = $this->getMockSession($queue);
        $session->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $session->onRequestSuccess(Argument::type(RequestInterface::class),
            Argument::type(ResponseInterface::class))->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testItemIsCompletedOnFailure()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com'));
        $client = $this->mockClient([new Response(400)]);

        $session = $this->getMockSession($queue);

        $session->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $session->onRequestFailure(Argument::type(RequestInterface::class),
            Argument::type(ResponseInterface::class))->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testExceptionEventIsFiredOnSuccesfulResponseException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com'));
        $client = $this->mockClient([new Response(200)]);
        $session = $this->getMockSession($queue);

        $session->onRequestSending(Argument::any(), Argument::any())
            ->shouldBeCalled();
        $session->onRequestSuccess(Argument::any(), Argument::any())
            ->willThrow(new \Exception('foo'));
        $session->onRequestException(Argument::type(RequestInterface::class),
            Argument::type(\Exception::class),
            Argument::type(ResponseInterface::class))->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testExceptionEventIsFiredOnFailureResponseException()
    {
        $client = $this->mockClient([new Response(400)]);
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com'));

        $session = $this->getMockSession($queue);

        $session->onRequestSending(Argument::any(), Argument::any())
            ->shouldBeCalled();
        $session->onRequestFailure(Argument::type(RequestInterface::class),
            Argument::type(ResponseInterface::class))
            ->willThrow(new \Exception('foo'));
        $session->onRequestException(Argument::type(RequestInterface::class),
            Argument::type(\Exception::class),
            Argument::type(ResponseInterface::class))->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client);
        $crawler->start(1)->wait();
        $this->assertEquals(1, $queue->count($queue::COMPLETE));
    }

    public function testExceptionEventIsFiredOnSendingException()
    {
        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com'));
        $client = $this->mockClient([new Response(400)]);

        $session = $this->getMockSession($queue);

        $session->onRequestSending(Argument::any(), Argument::any())
            ->willThrow(new \Exception('foo'));
        $session->onRequestException(Argument::any(),
            Argument::type('Exception'), null)->shouldBeCalled();

        $crawler = new Crawler($session->reveal(), $client);
        $crawler->start(1)->wait();

        // @todo: Should this job be completed?
//        $this->assertEquals(1, $queue->count(Job::COMPLETE));
    }

    public function testQueueIsWorkedUntilEmpty()
    {
        $responses = array_fill(0, 2, new Response(200));
        $count = 0;

        $queue = new ArrayRequestQueue();
        $queue->push(new Request('GET', 'https://lastcallmedia.com/1'));
        $client = $this->mockClient($responses);
        $session = $this->getMockSession($queue);

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

        $crawler = new Crawler($session->reveal(), $client);
        $crawler->start(5)->wait();

        $this->assertEquals(2, $count);
    }
}
