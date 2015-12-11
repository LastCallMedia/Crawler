<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerEvent;
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
        $urlHandler = new URLHandler('http://google.com');

        $request = new Request('GET', 'http://google.com');
        $event = new CrawlerResponseEvent($request, $response, $urlHandler);

        $subscriber = new LinkSubscriber();
        $subscriber->onCrawlerSuccess($event);
        $requestsAdded = $event->getAdditionalRequests();

        $added = array();
        foreach($requestsAdded as $requestAdded) {
            $added[] = (string)$requestAdded->getUri();
        }
        $this->assertEquals($links, $added);

    }
}