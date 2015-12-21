<?php


namespace LastCall\Crawler\Test\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;

class RedirectDiscovererTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testAddsRedirectsToSession()
    {
        $matcher = new Matcher();
        $normalizer = new Normalizer();

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response);

        $handler = new RedirectDiscoverer($matcher, $normalizer);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $added = $event->getAdditionalRequests();
        $this->assertCount(1, $added);
        $this->assertEquals('http://google.com/foo', $added[0]->getUri());
    }

}