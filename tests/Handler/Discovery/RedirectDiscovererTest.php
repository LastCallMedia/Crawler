<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RedirectDiscovererTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function getDiscoversRedirectTests()
    {
        return [
            [new Response(200), []],
            [new Response(301), []],
            [new Response(301, ['Location' => '/foo']), ['http://google.com/foo']],
        ];
    }

    /**
     * @dataProvider getDiscoversRedirectTests
     */
    public function testDiscoversRedirect($response, $expected)
    {
        $discoverer = new RedirectDiscoverer(new Normalizer());
        $event = new CrawlerResponseEvent(new Request('GET', 'http://google.com'), $response);
        $dispatcher = new EventDispatcher();

        $bubbledDown = $bubbledUp = [];
        $dispatcher->addListener(CrawlerEvents::URIS_DISCOVERED, function (CrawlerUrisDiscoveredEvent $e) use (&$bubbledDown) {
            foreach ($e->getDiscoveredUris() as $uri) {
                $bubbledDown[] = (string) $uri;
                $e->addAdditionalRequest(new Request('GET', $uri));
            }
        });

        $dispatcher->addSubscriber($discoverer);
        $dispatcher->dispatch(CrawlerEvents::SUCCESS, $event);

        foreach ($event->getAdditionalRequests() as $request) {
            $bubbledUp[] = (string) $request->getUri();
        }

        $this->assertEquals($expected, $bubbledDown);
        $this->assertEquals($expected, $bubbledUp);
    }

    public function testNormalizer()
    {
        $normalizer = new Normalizer([
            function () {
                return new Uri('bar');
            },
        ]);
        $discoverer = new RedirectDiscoverer($normalizer);
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($discoverer);
        $dispatcher->addListener(CrawlerEvents::URIS_DISCOVERED, function (CrawlerUrisDiscoveredEvent $event) {
            $this->assertEquals([new Uri('bar')], $event->getDiscoveredUris());
        });

        $event = new CrawlerResponseEvent(
            new Request('GET', 'http://google.com'),
            new Response(301, ['Location' => '/foo'])
        );

        $dispatcher->dispatch(CrawlerEvents::SUCCESS, $event);
    }
}
