<?php

namespace LastCall\Crawler\Test\Handler\Uri;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Handler\Uri\UriRecursor;
use LastCall\Crawler\Uri\Matcher;
use Psr\Http\Message\UriInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UriRecursorTest extends \PHPUnit_Framework_TestCase
{
    public function testReaddsUrisOnUri()
    {
        $dispatcher = new EventDispatcher();
        $event = new CrawlerUrisDiscoveredEvent(
            new Request('GET', 'http://google.com'),
            new Response(200),
            [new Uri('http://google.com')]
        );
        $recursor = new UriRecursor(Matcher::all()->always());
        $dispatcher->addSubscriber($recursor);
        $dispatcher->dispatch(CrawlerEvents::URIS_DISCOVERED, $event);
        $this->assertEquals([new Request('GET', 'http://google.com')], $event->getAdditionalRequests());
    }

    public function testUsesRequestFactory()
    {
        $dispatcher = new EventDispatcher();
        $event = new CrawlerUrisDiscoveredEvent(
            new Request('GET', 'http://google.com'),
            new Response(200),
            [new Uri('http://google.com')]
        );
        $recursor = new UriRecursor(Matcher::all()->always(), function (UriInterface $uri) {
            return new Request('HEAD', $uri);
        });
        $dispatcher->addSubscriber($recursor);
        $dispatcher->dispatch(CrawlerEvents::URIS_DISCOVERED, $event);
        $this->assertEquals([new Request('HEAD', 'http://google.com')], $event->getAdditionalRequests());
    }

    public function testUsesMatcher()
    {
        $dispatcher = new EventDispatcher();
        $event = new CrawlerUrisDiscoveredEvent(
            new Request('GET', 'http://google.com'),
            new Response(200),
            [new Uri('http://google.com')]
        );
        $recursor = new UriRecursor(Matcher::all()->never());
        $dispatcher->addSubscriber($recursor);
        $dispatcher->dispatch(CrawlerEvents::URIS_DISCOVERED, $event);
        $this->assertEquals([], $event->getAdditionalRequests());
    }
}
