<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RedirectSubscriber;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;

class RedirectSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testAddsRedirectsToSession()
    {
        $crawler = $this->prophesize(Crawler::class);
        $crawler->getUrlHandler('http://google.com')
          ->willReturn(new URLHandler('http://google.com'));

        $crawler->addRequest(Argument::that(function($request) {
            return 'http://google.com/foo' === (string) $request->getUri();
        }))->shouldBeCalled();

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($crawler->reveal(), $request, $response);

        $subscriber = new RedirectSubscriber();
        $subscriber->onResponse($event);
    }
}