<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RetryUrlSubscriber;
use LastCall\Crawler\Url\TraceableUri;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use LastCall\Crawler\Queue\RequestQueueInterface;

class RetryUrlSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testRetriesOnFailure()
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $urlHandler = new URLHandler('http://google.com');

        $queue->push(Argument::that(function($request) {
            return 'http://google.com/index.html' === (string) $request->getUri();
        }));

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(404);

        $event = new CrawlerResponseEvent(
            $request,
            $response,
            $queue->reveal(),
            $urlHandler
        );

        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerFail($event);
    }

    public function testRetryOnRedirectToOriginal()
    {
        $urlHandler = new URLHandler('http://google.com');
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push(Argument::that(function($request) {
            return 'http://google.com/index.html' === (string) $request->getUri();
        }))->shouldBeCalled();

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(301,
          ['Location' => 'http://google.com/index.html']);

        $event = new CrawlerResponseEvent(
            $request,
            $response,
            $queue->reveal(),
            $urlHandler
        );

        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerSuccess($event);
    }

    public function testNoRetryOnRedirectToAnyOther()
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->push()->shouldNotBeCalled();

        $urlHandler = new URLHandler('http://google.com');

        $crawler = $this->prophesize(Crawler::class);
        $crawler->addRequest()->shouldNotBeCalled();

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(301, ['Location' => 'http://google.com/foo']);

        $event = new CrawlerResponseEvent(
            $request,
            $response,
            $queue->reveal(),
            $urlHandler
        );

        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerSuccess($event);
    }
}