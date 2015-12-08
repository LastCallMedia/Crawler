<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Listener\LinkSubscriber;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;

class LinkSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function getLinkInputs()
    {
        return array(
          array(
            new Response(200, [], '<html><body><a href="/foo">Test</a></body>'),
            ['http://google.com/foo']
          ),
          array(
            new Response(301, [], '<html><body><a href="/foo">Test</a></body>'),
            []
          ),
        );
    }

    /**
     * @dataProvider getLinkInputs
     */
    public function testLinkScan($response, array $links)
    {
        $crawler = $this->prophesize(Crawler::class);
        $urlHandler = new URLHandler('http://google.com', 'http://google.com');
        $crawler->getUrlHandler('http://google.com')->willReturn($urlHandler);

        foreach($links as $link) {
            $crawler->addRequest(Argument::that(function(Request $request) use ($link) {
                return $link === (string) $request->getUri();
            }))->shouldBeCalled();
        }

        $request = new Request('GET', 'http://google.com');
        $event = new CrawlerResponseEvent($crawler->reveal(), $request, $response);
        $subscriber = new LinkSubscriber();
        $subscriber->onCrawlerSuccess($event);
    }
}