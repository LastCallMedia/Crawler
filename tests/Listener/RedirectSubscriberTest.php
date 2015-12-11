<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RedirectSubscriber;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;

class RedirectSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testAddsRedirectsToSession()
    {
        $urlHandler = new URLHandler('http://google.com');

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response, $urlHandler);

        $subscriber = new RedirectSubscriber();
        $subscriber->onResponse($event);

        $added = $event->getAdditionalRequests();
        $this->assertCount(1, $added);
        $this->assertEquals('http://google.com/foo', $added[0]->getUri());
    }
}