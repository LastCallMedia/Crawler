<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\LinkSubscriber;
use LastCall\Crawler\Queue\RequestQueueInterface;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function getLinkInputs()
    {
        return array(
            array(
                new Response(200, [],
                    '<html><body><a href="/foo">Test</a></body>'),
                ['http://google.com/foo']
            ),
            array(
                new Response(301, [],
                    '<html><body><a href="/foo">Test</a></body>'),
                []
            ),
        );
    }

    /**
     * @dataProvider getLinkInputs
     */
    public function testLinkScan($response, array $links)
    {
        $queue = $this->prophesize(RequestQueueInterface::class);
        $urlHandler = new URLHandler('http://google.com');

        foreach ($links as $link) {
            $queue->push(Argument::that(function ($request) use ($link) {
                return $link === (string) $request->getUri();
            }))->shouldBeCalled();
        }

        $request = new Request('GET', 'http://google.com');
        $event = $this->prophesize(CrawlerResponseEvent::class);
        $event->getQueue()->willReturn($queue);
        $event->getUrlHandler()->willReturn($urlHandler);
        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($response);
        $event->getDom()
            ->willReturn(new DomCrawler((string) $response->getBody()));

        $subscriber = new LinkSubscriber();
        $subscriber->onCrawlerSuccess($event->reveal());
    }
}