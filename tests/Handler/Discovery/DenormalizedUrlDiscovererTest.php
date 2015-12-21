<?php


namespace LastCall\Crawler\Test\Handler\Discovery;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Discovery\DenormalizedUrlDiscoverer;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Uri\TraceableUri;

class DenormalizedUrlDiscovererTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testRetriesOnFailure()
    {
        $originalUri = new TraceableUri(new Uri('http://google.com/index.html'));
        $uri = $originalUri->withPath('');
        $request = new Request('GET', $uri);
        $response = new Response(404);

        $handler = new DenormalizedUrlDiscoverer();

        $event = new CrawlerResponseEvent($request, $response);
        $this->invokeEvent($handler, CrawlerEvents::FAILURE, $event);

        $added = $event->getAdditionalRequests();
        $this->assertCount(1, $added);
        $this->assertEquals('http://google.com/index.html',
            $added[0]->getUri());
    }

    public function getRetryTests()
    {
        $tests = array();

        $tests[] = array(
            (new TraceableUri(new Uri('https://lastcallmedia.com/index.html')))->withPath(''),
            'https://lastcallmedia.com/index.html',
            'https://lastcallmedia.com/index.html'
        );
        $tests[] = array(
            (new TraceableUri(new Uri('https://lastcallmedia.com/index.html')))->withPath(''),
            'https://lastcallmedia.com/some/other.html',
            false
        );
        $tests[] = array(
            (new TraceableUri(new Uri('https://lastcallmedia.com/index.html')))->withPath(''),
            '/index.html',
            'https://lastcallmedia.com/index.html',
        );

        return $tests;
    }

    /**
     * @dataProvider getRetryTests
     */
    public function testRetry(TraceableUri $uri, $location, $expected)
    {
        $request = new Request('GET', $uri);
        $response = new Response(301, ['Location' => $location]);

        $event = new CrawlerResponseEvent($request, $response);

        $handler = new DenormalizedUrlDiscoverer();
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);
        $added = $event->getAdditionalRequests();
        if ($expected) {
            $this->assertCount(1, $added);
            $this->assertEquals($expected, (string)$added[0]->getUri());
        } else {
            $this->assertCount(0, $added);
        }

    }
}