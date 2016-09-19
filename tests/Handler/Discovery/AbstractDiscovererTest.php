<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Event\CrawlerHtmlResponseEvent;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use LastCall\Crawler\Uri\NormalizerInterface;

abstract class AbstractDiscovererTest extends \PHPUnit_Framework_TestCase
{
    abstract public function getDiscoveryTests();

    abstract public function getDiscoverer(NormalizerInterface $normalizer);

    /**
     * @dataProvider getDiscoveryTests
     */
    public function testDiscovery($html, $expectedUris, $expectedContext)
    {
        $dispatcher = new EventDispatcher();
        $subscriber = $this->getDiscoverer(new Normalizer());

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

    /**
     * @dataProvider getDiscoveryTests
     */
    public function testCallsNormalizer($html, $expectedUris, $expectedContext)
    {
        $dispatcher = new EventDispatcher();
        $subscriber = $this->getDiscoverer(new Normalizer([
            function () {
                return new Uri('foo');
            },
        ]));

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
        $expectedNormalizedUris = $expectedUris ?
            array_fill(0, count($expectedUris), new Uri('foo')) :
            [];

        $this->assertEquals($expectedNormalizedUris, $bubbledDown);
        $this->assertEquals($expectedNormalizedUris, $bubbledUp);
    }
}
