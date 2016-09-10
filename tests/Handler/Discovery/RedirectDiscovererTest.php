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
use Psr\Http\Message\UriInterface;

class RedirectDiscovererTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testNormalizer()
    {
        $normalizer = new Normalizer([
            function (UriInterface $uri) {
                return $uri->withFragment('test');
            },
        ]);
        $matcher = Matcher::all()->always();
        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response);

        $handler = new RedirectDiscoverer($matcher, $normalizer);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $expectedRequests = [
            new Request('GET', 'http://google.com/foo#test'),
        ];

        $added = $event->getAdditionalRequests();
        $this->assertEquals($expectedRequests, $added);
    }

    public function getMatcherTests()
    {
        return [
            [Matcher::all()->always(), [new Request('GET', 'http://google.com/foo')]],
            [Matcher::all()->never(), []],
        ];
    }

    /**
     * @dataProvider getMatcherTests
     */
    public function testMatcher($matcher, $expectedRequests)
    {
        $normalizer = new Normalizer();

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response);

        $handler = new RedirectDiscoverer($matcher, $normalizer);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $added = $event->getAdditionalRequests();
        $this->assertEquals($expectedRequests, $added);
    }

    public function getFactoryTests()
    {
        return [
            [
                function () {
                },
                [],
            ],
            [
                function () {
                    return new Request('GET', 'foo');
                },
                [new Request('GET', 'foo')],
            ],
        ];
    }

    /**
     * @dataProvider getFactoryTests
     */
    public function testFactory($factory, $expectedRequests)
    {
        $matcher = Matcher::all()->always();
        $normalizer = new Normalizer();

        $request = new Request('GET', 'http://google.com');
        $response = new Response(301, ['Location' => '/foo']);
        $event = new CrawlerResponseEvent($request, $response);

        $handler = new RedirectDiscoverer($matcher, $normalizer, $factory);
        $this->invokeEvent($handler, CrawlerEvents::SUCCESS, $event);

        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }
}
