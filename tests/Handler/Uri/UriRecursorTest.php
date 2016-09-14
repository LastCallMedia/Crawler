<?php

namespace LastCall\Crawler\Test\Handler\Uri;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Event\CrawlerUrisDiscoveredEvent;
use LastCall\Crawler\Handler\Uri\UriRecursor;
use LastCall\Crawler\Uri\Matcher;
use Psr\Http\Message\UriInterface;

class UriRecursorTest extends \PHPUnit_Framework_TestCase
{
    public function testReaddsUrisOnUri()
    {
        $event = $this->prophesize(CrawlerUrisDiscoveredEvent::class);
        $event->getDiscoveredUris()->willReturn([new Uri('http://google.com')]);

        $event->addAdditionalRequest(new Request('GET', new Uri('http://google.com')))->shouldBeCalled();

        $recurser = new UriRecursor(Matcher::all()->always());
        $recurser->onDiscovery($event->reveal());
    }

    public function testUsesRequestFactory()
    {
        $event = $this->prophesize(CrawlerUrisDiscoveredEvent::class);
        $event->getDiscoveredUris()->willReturn([new Uri('http://google.com')]);

        $event->addAdditionalRequest(new Request('HEAD', 'http://google.com'))->shouldBeCalled();

        $recursor = new UriRecursor(Matcher::all()->always(), function (UriInterface $uri) {
            return new Request('HEAD', $uri);
        });
        $recursor->onDiscovery($event->reveal());
    }

    public function testUsesMatcher()
    {
        $event = $this->prophesize(CrawlerUrisDiscoveredEvent::class);
        $event->getDiscoveredUris()->willReturn([new Uri('http://google.com')]);

        $event->addAdditionalRequest()->shouldNotBeCalled();
        $recursor = new UriRecursor(Matcher::all()->never());
        $recursor->onDiscovery($event->reveal());
    }
}
