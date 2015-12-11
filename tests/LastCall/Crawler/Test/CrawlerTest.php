<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Queue\Driver\ArrayDriver;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Queue\RequestQueue;
use LastCall\Crawler\Queue\RequestQueueInterface;
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

    protected function getMockSession($queue = NULL, $client = NULL) {
        if(!$queue) {
            $queue = $this->getMock(RequestQueueInterface::class);
        }
        if(!$client) {
            $client = $this->prophesize(ClientInterface::class);
        }

        $session = $this->prophesize(SessionInterface::class);
        $session->getStartUrl(Argument::type('string'))->willReturnArgument(0);
        $session->getQueue()->willReturn($queue);
        $session->getClient()->willReturn($client);
        $session->isFinished()->will(function() use ($queue) {
            return $queue->count() === 0;
        });
        return $session;
    }

    public function testTeardownEventIsDispatched() {
        $session = $this->getMockSession();
        $session->onTeardown()->shouldBeCalledTimes(1);

        $crawler = new Crawler($session->reveal());
        $crawler->teardown();
    }

    public function testSetupEventIsDispatched() {
        $session = $this->getMockSession();
        $session->onSetup()->shouldBeCalledTimes(1);

        $crawler = new Crawler($session->reveal());
        $crawler->setUp();
    }

    public function testItemIsCompletedOnSuccess()
    {
        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $client = $this->mockClient([new Response(200)]);

        $session = $this->getMockSession($queue, $client);

        $session->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $session->onRequestSuccess(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $crawler = new Crawler($session->reveal());
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $queue->count(Job::COMPLETE));
    }


    public function testItemIsCompletedOnFailure()
    {
        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $client = $this->mockClient([new Response(400)]);

        $session = $this->getMockSession($queue, $client);

        $session->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $session->onRequestFailure(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $crawler = new Crawler($session->reveal());
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $queue->count(Job::COMPLETE));
    }

    public function testExceptionEventIsFiredOnSuccesfulResponseException()
    {
        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $client = $this->mockClient([new Response(200)]);
        $session = $this->getMockSession($queue, $client);

        $session->onRequestSending(Argument::any(), Argument::any())
            ->shouldBeCalled();
        $session->onRequestSuccess(Argument::any(), Argument::any())
            ->willThrow(new \Exception('foo'));
        $session->onRequestException(Argument::type(RequestInterface::class), Argument::type(\Exception::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $crawler = new Crawler($session->reveal());
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $queue->count(Job::COMPLETE));
    }

    public function testExceptionEventIsFiredOnFailureResponseException()
    {
        $client = $this->mockClient([new Response(400)]);
        $queue = new RequestQueue(new ArrayDriver(), 'request');

        $session = $this->getMockSession($queue, $client);

        $session->onRequestSending(Argument::any(), Argument::any())
            ->shouldBeCalled();
        $session->onRequestFailure(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->willThrow(new \Exception('foo'));
        $session->onRequestException(Argument::type(RequestInterface::class), Argument::type(\Exception::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $crawler = new Crawler($session->reveal());
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $queue->count(Job::COMPLETE));
    }


    public function testExceptionEventIsFiredOnSendingException()
    {
        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $client = $this->mockClient([new Response(400)]);

        $session = $this->getMockSession($queue, $client);

        $session->onRequestSending(Argument::any(), Argument::any())
            ->willThrow(new \Exception('foo'));
        $session->onRequestException(Argument::any(), Argument::type('Exception'), NULL)
            ->shouldBeCalled();

        $crawler = new Crawler($session->reveal());
        $crawler->start(1, 'http://google.com')->wait();

        // @todo: Should this job be completed?
//        $this->assertEquals(1, $queue->count(Job::COMPLETE));
    }

    public function testQueueIsWorkedUntilEmpty() {
        $responses = array_fill(0, 2, new Response(200));
        $count = 0;

        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $client = $this->mockClient($responses);
        $session = $this->getMockSession($queue, $client);

        $session->onRequestSending(Argument::type(RequestInterface::class))->shouldBeCalled();
        $session->onRequestSuccess(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->will(function($args) use (&$queue, &$count) {
                $request = $args[0];
                if($request->getUri() == 'http://google.com/1') {
                    $queue->push(new Request('GET', 'http://google.com/2'));
                }
                $count++;
            });

        $crawler = new Crawler($session->reveal());
        $crawler->start(5, 'http://google.com/1')->wait();

        $this->assertEquals(2, $count);
    }
}