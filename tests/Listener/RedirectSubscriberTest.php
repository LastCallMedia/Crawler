<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\RedirectSubscriber;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;

class RedirectSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testAddsRedirectsToSession()
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $urlHandler = new URLHandler('http://google.com');
        $queue->push(Argument::that(function ($request) {
            return 'http://google.com/foo' === (string) $request->getUri();
        }))->shouldBeCalled();

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response, $queue->reveal(),
            $urlHandler);

        $subscriber = new RedirectSubscriber();
        $subscriber->onResponse($event);
    }
}