<?php

namespace LastCall\Crawler\Test\Event;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerExceptionEvent;

class CrawlerExceptionEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRequest()
    {
        $request = new Request('GET', 'test');
        $event = new CrawlerExceptionEvent($request, new Response(200), new \Exception());
        $this->assertSame($request, $event->getRequest());
    }

    public function testGetResponse()
    {
        $response = new Response(200);
        $event = new CrawlerExceptionEvent(new Request('GET', 'test'), $response, new \Exception());
        $this->assertSame($response, $event->getResponse());
    }

    public function testResponseOptional()
    {
        $event = new CrawlerExceptionEvent(new Request('GET', 'test'), null, new \Exception());
        $this->assertNull($event->getResponse());
    }
}
