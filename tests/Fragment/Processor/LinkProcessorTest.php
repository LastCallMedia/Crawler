<?php

namespace LastCall\Crawler\Test\Fragment\Processor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ProcessesTestFragments;

    private static $stdHtml = '<html><a href="/foo"></a></html>';

    public function getInputs()
    {
        $inputs = [
            [
                '<html><a href="/foo"></a></html>',
                [new Request('GET', 'https://lastcallmedia.com/foo')],
            ],
            [
                '<html><a href="https://lastcallmedia.com/bar">Test</a></html>',
                [new Request('GET', 'https://lastcallmedia.com/bar')],
            ],
        ];

        return $inputs;
    }

    /**
     * @dataProvider getInputs
     */
    public function testProcessLinks($html, $expectedRequests)
    {
        $processor = new LinkProcessor(
            Matcher::all()->always(),
            new Normalizer()
        );
        $event = $this->fireSuccess(
            $processor,
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], $html)
        );
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
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
                    return new Request('HEAD', 'http://google.com');
                },
                [new Request('HEAD', 'http://google.com')],
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

        $processor = new LinkProcessor($matcher, $normalizer, $factory);

        $event = $this->fireSuccess(
            $processor,
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], self::$stdHtml)
        );
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }

    public function getMatcherTests()
    {
        return [
            [Matcher::all()->always(), [new Request('GET', 'https://lastcallmedia.com/foo')]],
            [Matcher::all()->never(), []],
        ];
    }

    /**
     * @dataProvider getMatcherTests
     */
    public function testMatcher($matcher, $expectedRequests)
    {
        $normalizer = new Normalizer();

        $processor = new LinkProcessor($matcher, $normalizer);
        $event = $this->fireSuccess(
            $processor,
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], self::$stdHtml)
        );
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }

    public function testNormalizer()
    {
        $matcher = Matcher::all()->always();
        $normalizer = new Normalizer([
            function (UriInterface $uri) {
                return $uri->withFragment('test');
            },
        ]);

        $processor = new LinkProcessor($matcher, $normalizer);
        $event = $this->fireSuccess(
            $processor,
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], self::$stdHtml)
        );
        $expectedRequests = [
            new Request('GET', 'https://lastcallmedia.com/foo#test'),
        ];
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }
}
