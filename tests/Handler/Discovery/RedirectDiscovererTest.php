<?php


namespace LastCall\Crawler\Test\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Url\URLHandler;

class RedirectDiscovererTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testAddsRedirectsToSession()
    {
        $urlHandler = new URLHandler('http://google.com');

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response, $urlHandler);

        $handler = new RedirectDiscoverer($urlHandler);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $added = $event->getAdditionalRequests();
        $this->assertCount(1, $added);
        $this->assertEquals('http://google.com/foo', $added[0]->getUri());
    }

}