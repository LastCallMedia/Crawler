<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RetryUrlSubscriber;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Url\TraceableUri;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;

class RetryUrlSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testRetriesOnFailure()
    {
        $urlHandler = new URLHandler('http://google.com');

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(404);

        $event = new CrawlerResponseEvent($request, $response,
            $urlHandler);

        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerFail($event);
        $added = $event->getAdditionalRequests();
        $this->assertCount(1, $added);
        $this->assertEquals('http://google.com/index.html', (string) $added[0]->getUri());
    }

    public function testRetryOnRedirectToOriginal()
    {
        $urlHandler = new URLHandler('http://google.com');

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(301,
            ['Location' => 'http://google.com/index.html']);

        $event = new CrawlerResponseEvent($request, $response,
            $urlHandler);

        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerSuccess($event);

        $added = $event->getAdditionalRequests();
        $this->assertCount(1, $added);
        $this->assertEquals('http://google.com/index.html', $added[0]->getUri());
    }

    public function testNoRetryOnRedirectToAnyOther()
    {

        $urlHandler = new URLHandler('http://google.com');

        $crawler = $this->prophesize(Crawler::class);
        $crawler->addRequest()->shouldNotBeCalled();

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(301, ['Location' => 'http://google.com/foo']);

        $event = new CrawlerResponseEvent($request, $response,
            $urlHandler);

        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerSuccess($event);
        $this->assertCount(0, $event->getAdditionalRequests());
    }
}