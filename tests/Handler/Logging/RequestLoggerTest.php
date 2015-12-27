<?php

namespace LastCall\Crawler\Test\Handler\Logging;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use Psr\Log\LoggerInterface;

class RequestLoggerTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testSendingLogging()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RequestLogger($logger->reveal());
        $event = new CrawlerEvent($request);
        $this->invokeEvent($handler, CrawlerEvents::SENDING, $event);
        $logger->debug('Sending https://lastcallmedia.com', [
            'url' => 'https://lastcallmedia.com',
        ])->shouldHaveBeenCalled();
    }

    public function testSuccessLogging()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $response = new Response(200);
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RequestLogger($logger->reveal());
        $event = new CrawlerResponseEvent($request, $response);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);
        $logger->debug('Received https://lastcallmedia.com', [
            'url' => 'https://lastcallmedia.com',
            'status' => 200,
        ])->shouldHaveBeenCalled();
    }

    public function testSuccessLoggingRedirect()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $response = new Response(301, ['Location' => '/foo']);
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RequestLogger($logger->reveal());
        $event = new CrawlerResponseEvent($request, $response);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);
        $logger->info('Received https://lastcallmedia.com redirecting to /foo',
            [
                'url' => 'https://lastcallmedia.com',
                'status' => 301,
                'redirect' => '/foo',
            ])->shouldHaveBeenCalled();
    }

    public function testFailureLogging()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $response = new Response(400);
        $logger = $this->prophesize(LoggerInterface::class);
        $handler = new RequestLogger($logger->reveal());

        $event = new CrawlerResponseEvent($request, $response);
        $this->invokeEvent($handler, CrawlerEvents::FAILURE, $event);
        $logger->warning('Failure https://lastcallmedia.com', [
            'url' => 'https://lastcallmedia.com',
            'status' => 400,
        ])->shouldHaveBeenCalled();
    }
}
