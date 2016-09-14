<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class LinkDiscovererTest extends \PHPUnit_Framework_TestCase
{
    public function getDiscoveryTests()
    {
        return [
            ['<html></html>', []],
            ['<html><a href="/foo"></a></html>', ['https://lastcallmedia.com/foo']],
            ['<html><a href="/foo"></a><a href="https://lastcallmedia.com/foo"></a></html>', ['https://lastcallmedia.com/foo']],
        ];
    }

    /**
     * @dataProvider getDiscoveryTests
     */
    public function testDiscoversUris($html, $expected)
    {
        $dispatcher = new EventDispatcher();
        $discoverer = new LinkDiscoverer(new Normalizer());
        $dispatcher->addSubscriber($discoverer);

        $bubbledDown = $bubbledUp = [];
        $dispatcher->addListener(CrawlerEvents::URIS_DISCOVERED, function (CrawlerUrisDiscoveredEvent $e) use (&$bubbledDown) {
            foreach ($e->getDiscoveredUris() as $uri) {
                $bubbledDown[] = $uri;
                $e->addAdditionalRequest(new Request('GET', $uri));
            }
        });

        $event = new CrawlerHtmlResponseEvent(
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], $html)
        );
        $dispatcher->dispatch(CrawlerEvents::SUCCESS_HTML, $event);

        foreach ($event->getAdditionalRequests() as $request) {
            $bubbledUp[] = $request->getUri();
        }

        $this->assertEquals($expected, $bubbledDown);
        $this->assertEquals($expected, $bubbledUp);
    }

    public function testCallsNormalizer()
    {
        $dispatcher = new EventDispatcher();
        $discoverer = new LinkDiscoverer(new Normalizer([
            function () {
                return new Uri('bar');
            },
        ]));
        $dispatcher->addSubscriber($discoverer);

        $calls = [];
        $dispatcher->addListener('crawler.discover.link', function (CrawlerUrisDiscoveredEvent $e) use (&$calls) {
            $this->assertEquals('bar', (string) $e->getUri());
        });

        $event = new CrawlerHtmlResponseEvent(
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], '<html><a href="foo"></a></html>')
        );
        $dispatcher->dispatch(CrawlerEvents::SUCCESS_HTML, $event);
    }
}
