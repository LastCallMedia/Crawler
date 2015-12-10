<?php

namespace LastCall\Crawler\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Queue\Driver\ArrayDriver;
use LastCall\Crawler\Queue\Job;
use LastCall\Crawler\Queue\RequestQueue;
use Prophecy\Argument;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{


    protected function mockClient(array $requests)
    {
        $handler = new MockHandler($requests);

        return new Client(['handler' => HandlerStack::create($handler)]);
    }

    protected function mockConfiguration(array $responses = [], array $requests = []) {
        $handler = new MockHandler($responses);
        $client = new Client(['handler' => HandlerStack::create($handler)]);
        $config = new Configuration('http://google.com');
        $config->setClient($client);

        foreach($requests as $request) {
            $config->getQueueDriver()->push(new Job('request', $request));
        }

        return $config;
    }

    protected function newMockCfg(array $responses) {
        $config = $this->prophesize(ConfigurationInterface::class);
        $queue = new RequestQueue(new ArrayDriver(), 'request');
        $handler = new MockHandler($responses);
        $client = new Client(['handler' => HandlerStack::create($handler)]);
        $config->getQueue()->willReturn($queue);
        $config->getClient()->willReturn($client);

        return $config;
    }

    public function testTeardownEventIsDispatched() {
        $config = $this->newMockCfg([]);
        $config->onTeardown()->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->teardown();
    }

    public function testSetupEventIsDispatched() {
        $config = $this->newMockCfg([]);
        $config->onSetup()->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->setUp();
    }

    public function testItemIsCompletedOnSuccess()
    {
        $configMock = $this->newMockCfg([new Response()]);
        $configMock->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $configMock->onRequestSuccess(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $config = $configMock->reveal();
        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $config->getQueue()->count(Job::COMPLETE));
    }

    public function testItemIsCompletedOnFailure()
    {
        $configMock = $this->newMockCfg([new Response(400)]);
        $configMock->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();
        $configMock->onRequestFailure(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $config = $configMock->reveal();
        $crawler = new Crawler($config);
        $crawler->start(1, 'http://google.com')->wait();
        $this->assertEquals(1, $config->getQueue()->count(Job::COMPLETE));
    }

    public function testSuccessEventIsFiredOnSuccess()
    {
        $config = $this->newMockCfg([new Response(200)]);
        $config->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();

        $config->onRequestSuccess(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->start(1, 'http://google.com')->wait();
    }

    public function testFailureEventIsFiredOnFailure()
    {
        $config = $this->newMockCfg([new Response(400)]);
        $config->onRequestSending(Argument::type(RequestInterface::class))
            ->shouldBeCalled();

        $config->onRequestFailure(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->start(1, 'http://google.com')->wait(FALSE);
    }

    public function testExceptionEventIsFiredOnSuccesfulResponseException()
    {
        $config = $this->newMockCfg([new Response(200)]);
        $config->onRequestSending(Argument::any(), Argument::any())
            ->shouldBeCalled();
        $config->onRequestSuccess(Argument::any(), Argument::any())
            ->willThrow(new \Exception('foo'));
        $config->onRequestException(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class), Argument::type(\Exception::class))
            ->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->start(1, 'http://google.com')->wait();
    }

    public function testExceptionEventIsFiredOnFailureResponseException()
    {
        $config = $this->newMockCfg([new Response(400)]);
        $config->onRequestSending(Argument::any(), Argument::any())
            ->shouldBeCalled();
        $config->onRequestFailure(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->willThrow(new \Exception('foo'));
        $config->onRequestException(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class), Argument::type(\Exception::class))
            ->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->start(1, 'http://google.com')->wait();
    }

    public function testExceptionEventIsFiredOnSendingException()
    {
        $config = $this->newMockCfg([new Response(400)]);
        $config->onRequestSending(Argument::any(), Argument::any())
            ->willThrow(new \Exception('foo'));
        $config->onRequestException(Argument::any(), Argument::any(), Argument::type('Exception'))
            ->shouldBeCalled();

        $crawler = new Crawler($config->reveal());
        $crawler->start(1, 'http://google.com')->wait();
    }

    public function testQueueIsWorkedUntilEmpty() {
        $responses = array_fill(0, 2, new Response(200));
        $count = 0;
        $configMock = $this->newMockCfg($responses);

        $configMock->onRequestSending(Argument::type(RequestInterface::class))->shouldBeCalled();

        $configMock->onRequestSuccess(Argument::type(RequestInterface::class), Argument::type(ResponseInterface::class))
            ->will(function($args) use (&$config, &$count) {
                $request = $args[0];
                if($request->getUri() == 'http://google.com/1') {
                    $config->getQueue()->push(new Request('GET', 'http://google.com/2'));
                }
                $count++;
            });

        $config = $configMock->reveal();
        $crawler = new Crawler($config);

        $promise = $crawler->start(5, 'http://google.com/1');

        $promise->wait();
        $this->assertEquals(2, $count);
    }
}