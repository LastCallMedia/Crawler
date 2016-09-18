<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Handler\Discovery\AssetDiscoverer;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AssetDiscovererTest extends \PHPUnit_Framework_TestCase
{
    public function getDiscoveryTests()
    {
        return [
            ['<html></html>', [], null],
            ['<html><img src="/foo.jpg" /></html>', ['http://google.com/foo.jpg'], 'image'],
            ['<html><img href="/foo.jpg" /></html>', [], null],
            ['<html><img src="/foo.jpg" /><img src="http://google.com/foo.jpg" /></html>', ['http://google.com/foo.jpg'], 'image'],

            ['<html><link rel="stylesheet" href="/foo.css" /></html>', ['http://google.com/foo.css'], 'stylesheet'],
            ['<html><link rel="stylesheet" src="/foo.css" /></html>', [], null],
            ['<html><link href="/foo.css" /></html>', [], null],
            ['<html><link rel="stylesheet" href="/foo.css" /><link rel="stylesheet" href="http://google.com/foo.css" /></html>', ['http://google.com/foo.css'], 'stylesheet'],

            ['<html><script type="text/javascript" src="/foo.js"></script></html>', ['http://google.com/foo.js'], 'script'],
            ['<html><script src="/foo.js"></script></html>', [], null],
            ['<html><script type="text/javascript" href="/foo.js"></script></html>', [], null],
            ['<html><script type="text/javascript" href="/foo.js"></script><script type="text/javascript" src="http://google.com/foo.css"></script></html>', ['http://google.com/foo.css'], 'script'],
        ];
    }

    /**
     * @dataProvider getDiscoveryTests
     */
    public function testDiscovery($html, $expectedUris, $expectedContext)
    {
        $dispatcher = new EventDispatcher();
        $subscriber = new AssetDiscoverer(new Normalizer());

        $bubbledDown = $bubbledUp = [];
        $dispatcher->addListener(CrawlerEvents::URIS_DISCOVERED, function (CrawlerUrisDiscoveredEvent $e) use (&$bubbledDown, $expectedContext) {
            foreach ($e->getDiscoveredUris() as $uri) {
                $bubbledDown[] = (string) $uri;
                $e->addAdditionalRequest(new Request('GET', $uri));
            }
            $this->assertEquals($expectedContext, $e->getContext());
        });
        $dispatcher->addSubscriber($subscriber);

        $event = new CrawlerHtmlResponseEvent(
            new Request('GET', 'http://google.com'),
            new Response(200, [], $html)
        );

        $dispatcher->dispatch(CrawlerEvents::SUCCESS_HTML, $event);
        foreach ($event->getAdditionalRequests() as $request) {
            $bubbledUp[] = (string) $request->getUri();
        }
        $this->assertEquals($expectedUris, $bubbledDown);
        $this->assertEquals($expectedUris, $bubbledUp);
    }

    public function testCallsNormalizer()
    {
        $dispatcher = new EventDispatcher();
        $subscriber = new AssetDiscoverer(new Normalizer([
            function () {
                return new Uri('bar');
            },
        ]));

        $dispatcher->addListener(CrawlerEvents::URIS_DISCOVERED, function (CrawlerUrisDiscoveredEvent $e) use (&$bubbledDown) {
            foreach ($e->getDiscoveredUris() as $uri) {
                $this->assertEquals('bar', (string) $uri);
            }
        });
        $dispatcher->addSubscriber($subscriber);

        $event = new CrawlerHtmlResponseEvent(
            new Request('GET', 'http://google.com'),
            new Response(200, [], '<html><img src="foo"/></html>')
        );

        $dispatcher->dispatch(CrawlerEvents::SUCCESS_HTML, $event);
    }
}
