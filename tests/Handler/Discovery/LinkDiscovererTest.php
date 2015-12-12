<?php


namespace LastCall\Crawler\Test\Handler\Discovery;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Url\URLHandler;

class LinkDiscovererTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function getInputs()
    {
        $inputs = array(
            array(
                new Response(200, [],
                    '<html><body><a href="/foo">Test</a></body>'),
                ['https://lastcallmedia.com/foo']
            ),
            array(
                new Response(200, [],
                    '<html><body><a href="https://lastcallmedia.com/bar">Test</a></body>'),
                ['https://lastcallmedia.com/bar']
            )
        );

        return $inputs;
    }

    /**
     * @dataProvider getInputs
     */
    public function testLinkDiscovery($response, $expected)
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $urlHandler = new URLHandler('https://lastcallmedia.com');
        $event = new CrawlerResponseEvent($request, $response, $urlHandler);
        $handler = new LinkDiscoverer();
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $added = [];
        foreach ($event->getAdditionalRequests() as $addedRequest) {
            $added[] = (string)$addedRequest->getUri();
        }
        $this->assertEquals($expected, $added);
    }

}