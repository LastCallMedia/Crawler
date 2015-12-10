<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RequestLogSubscriber;
use LastCall\Crawler\Url\TraceableUri;
use Psr\Log\LoggerInterface;

class RequestLogSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testLogsRequest()
    {
        $logger = $this->getMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with('Sending http://google.com', []);
        $subscriber = new RequestLogSubscriber($logger);

        $request = new Request('GET', 'http://google.com');

        $event = $this->prophesize(CrawlerEvent::class);
        $event->getRequest()->willReturn($request);
        $subscriber->onRequestSending($event->reveal());
    }

    public function testLogsResponse()
    {
        $logger = $this->getMock(LoggerInterface::class);
        $logger->expects($this->once())
          ->method('info')
          ->with('Received 200 for http://google.com', []);
        $subscriber = new RequestLogSubscriber($logger);

        $request = new Request('GET', 'http://google.com');
        $response = new Response(200);
        $event = $this->prophesize(CrawlerResponseEvent::class);
        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($response);

        $subscriber->onRequestComplete($event->reveal());
    }

    public function testLogsAlternateForms()
    {
        $logger = $this->getMock(LoggerInterface::class);
        $logger->expects($this->once())
          ->method('info')
          ->with('Received 200 for http://google.com', [
              'previous' => 'http://google.com/index.html',
              'next' => '',
          ]);
        $subscriber = new RequestLogSubscriber($logger);

        $uri = new TraceableUri(new Uri('http://google.com/index.html'));
        $request = new Request('GET', $uri->withPath(''));
        $response = new Response(200);

        $event = $this->prophesize(CrawlerResponseEvent::class);
        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($response);

        $subscriber->onRequestComplete($event->reveal());
    }

}