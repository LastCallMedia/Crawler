<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Entity\LinkedURL;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RetryUrlSubscriber;
use LastCall\Crawler\Url\TraceableUri;
use Prophecy\Argument;

class RetryUrlSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testRetriesOnFailure()
    {
        $crawler = $this->prophesize(Crawler::class);
        $crawler->addRequest(Argument::that(function($request) {
            return 'http://google.com/index.html' === (string) $request->getUri();
        }))->shouldBeCalled();

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(404);

        $event = new CrawlerResponseEvent($crawler->reveal(), $request, $response);
        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerFail($event);
    }

    public function testRetryOnRedirectToOriginal()
    {
        $crawler = $this->prophesize(Crawler::class);
        $crawler->addRequest(Argument::that(function($request) {
            return 'http://google.com/index.html' === (string) $request->getUri();
        }))->shouldBeCalled();
        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(301,
          ['Location' => 'http://google.com/index.html']);

        $event = new CrawlerResponseEvent($crawler->reveal(), $request, $response);
        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerSuccess($event);
    }

    public function testNoRetryOnRedirectToAnyOther()
    {
        $crawler = $this->prophesize(Crawler::class);
        $crawler->addRequest()->shouldNotBeCalled();

        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(301, ['Location' => 'http://google.com/foo']);

        $event = new CrawlerResponseEvent($crawler->reveal(), $request, $response);
        $subscriber = new RetryUrlSubscriber();
        $subscriber->onCrawlerSuccess($event);
    }
}