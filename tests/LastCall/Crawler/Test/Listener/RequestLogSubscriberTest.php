<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Crawler;
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
        $crawler = $this->prophesize(Crawler::class)->reveal();
        $event = new CrawlerEvent($crawler, $request);
        $subscriber->onRequestSending($event);
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
        $crawler = $this->prophesize(Crawler::class)->reveal();
        $event = new CrawlerResponseEvent($crawler, $request, $response);
        $subscriber->onRequestComplete($event);
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
        $crawler = $this->prophesize(Crawler::class)->reveal();
        $event = new CrawlerResponseEvent($crawler, $request, $response);

        $subscriber->onRequestComplete($event);
    }

}