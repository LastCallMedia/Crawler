<?php

namespace LastCall\Crawler\Test\Url;

use LastCall\Crawler\Url\CachedUrlHandler;
use LastCall\Crawler\Url\Matcher;
use LastCall\Crawler\Url\NormalizerInterface;
use LastCall\Crawler\Url\URLHandler;
use Prophecy\Argument;
use Prophecy\Prediction\CallPrediction;
use Prophecy\Promise\ReturnArgumentPromise;

class URLHandlerTest extends \PHPUnit_Framework_TestCase
{


    public function testGetBaseUrl()
    {
        $handler = new URLHandler('foo', 'bar');
        $this->assertEquals('foo', $handler->getBaseUrl());
        $this->assertEquals('bar', $handler->getCurrentUrl());
    }

    public function getRelativeUrls()
    {
        return array(
            array(
                'http://google.com',
                'http://google.com/search',
                'https://newegg.com',
                'https://newegg.com'
            ),
            array(
                'http://google.com',
                'http://google.com/search',
                '/relative1',
                'http://google.com/relative1'
            ),
            array(
                'http://google.com',
                'http://google.com/search',
                '/relative1',
                'http://google.com/relative1'
            ),
            array(
                'http://google.com',
                'http://google.com/search/',
                'relative2',
                'http://google.com/search/relative2'
            ),
            array(
                'http://google.com',
                'http://google.com/search/',
                '//google.com/schemerelative',
                'http://google.com/schemerelative'
            ),
            array(
                'http://google.com',
                'http://google.com/search/',
                '#foo',
                'http://google.com/search/#foo'
            ),
            array(
                'http://google.com',
                'http://google.com/search/',
                'mailto:joe@blow.com',
                false
            ),
            array(
                'http://google.com',
                'http://google.com/search/',
                'javascript:alert()',
                false
            ),
        );
    }

    /**
     * @dataProvider getRelativeUrls
     */
    public function testAbsolutize($base, $current, $toProcess, $expected)
    {
        $handler = new URLHandler($base, $current);
        $this->assertEquals($expected,
            (string)$handler->absolutizeUrl($toProcess));
    }

    /**
     * @dataProvider getRelativeUrls
     */
    public function testAbsolutizeCachedUrl(
        $base,
        $current,
        $toProcess,
        $expected
    ) {
        $handler = new CachedUrlHandler($base, $current);
        $this->assertEquals($expected,
            (string)$handler->absolutizeUrl($toProcess));
    }

    /**
     * @dataProvider getRelativeUrls
     */
    public function testNormalize($base, $current, $toProcess, $expected)
    {
        $normalizer = $this->prophesize(NormalizerInterface::class);
        $normalizer->normalize(Argument::any())
            ->should(new CallPrediction())
            ->will(new ReturnArgumentPromise());
        $handler = new URLHandler($base, $current, null, $normalizer->reveal());
        $this->assertEquals($expected,
            (string)$handler->normalizeUrl($toProcess));
    }

    /**
     * @dataProvider getRelativeUrls
     */
    public function testNormalizeCached(
        $base,
        $current,
        $toProcess,
        $expected
    ) {
        $normalizer = $this->prophesize(NormalizerInterface::class);
        $normalizer->normalize(Argument::any())
            ->should(new CallPrediction())
            ->will(new ReturnArgumentPromise());
        $handler = new CachedUrlHandler($base, $current, null,
            $normalizer->reveal());
        $this->assertEquals($expected,
            (string)$handler->normalizeUrl($toProcess));
    }

    public function testIncludesUrl()
    {
        $matcher = $this->prophesize(Matcher::class);
        $matcher->matches('http://google.com')
            ->shouldBeCalled()
            ->willReturn(true);
        $handler = new URLHandler('http://google.com', null,
            $matcher->reveal());
        $this->assertEquals(true, $handler->includesUrl('http://google.com'));
    }

    public function testShouldCrawl()
    {
        $matcher = $this->prophesize(Matcher::class);
        $matcher->matchesHtml('http://google.com')
            ->shouldBeCalled()
            ->willReturn(true);
        $handler = new URLHandler('http://example.com', null,
            $matcher->reveal());
        $this->assertEquals($handler->isCrawlable('http://google.com'), true);
    }

    public function testIsFile()
    {
        $matcher = $this->prophesize(Matcher::class);
        $matcher->matchesFile('http://google.com/test.txt')
            ->shouldBeCalled()
            ->willReturn(true);
        $handler = new URLHandler('http://example.com', null,
            $matcher->reveal());
        $this->assertEquals(true,
            $handler->isFile('http://google.com/test.txt'));
    }
}