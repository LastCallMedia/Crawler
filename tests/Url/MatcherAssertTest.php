<?php

namespace LastCall\Crawler\Test\Url;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\MatcherAssert;

class MatcherAssertTest extends \PHPUnit_Framework_TestCase
{
    public function testAlways()
    {
        $always = MatcherAssert::always();
        $this->assertTrue($always(new Uri('foo')));
    }

    public function testNever()
    {
        $never = MatcherAssert::never();
        $this->assertFalse($never(new Uri('foo')));
    }

    public function schemeIsTests()
    {
        return [
            ['http', 'http://test.com', true],
            ['http', 'https://test.com', false],
            [['http'], 'http://test.com', true],
        ];
    }

    /**
     * @dataProvider schemeIsTests
     */
    public function testSchemeIs($schemes, $uri, $expected)
    {
        $uri = new Uri($uri);
        $schemeIs = MatcherAssert::schemeIs($schemes);
        $this->assertEquals($expected, $schemeIs($uri));
    }

    public function schemeMatchesTests()
    {
        return [
            ['/^http/', 'http://lastcallmedia.com', true],
            ['/^http/', 'https://lastcallmedia.com', true],
            ['/^http/', 'ftp://lastcallmedia.com', false],
            [['/^http/'], 'http://lastcallmedia.com', true],
        ];
    }

    /**
     * @dataProvider schemeMatchesTests
     */
    public function testSchemeMatches($patterns, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::schemeMatches($patterns);
        $this->assertEquals($expected, $m($uri));
    }

    public function hostIsTests()
    {
        return [
            ['lastcallmedia.com', 'http://google.com', false],
            ['lastcallmedia.com', 'http://lastcallmedia.com', true],
            [['lastcallmedia.com', 'lcm.io'], 'http://lcm.io', true],
        ];
    }

    /**
     * @dataProvider hostIsTests
     */
    public function testHostIs($hosts, $uri, $expected)
    {
        $uri = new Uri($uri);
        $hostIs = MatcherAssert::hostIs($hosts);
        $this->assertEquals($expected, $hostIs($uri));
    }

    public function hostMatchesTests()
    {
        return [
            ['/lastcallmedia\.com/', 'https://lastcallmedia.com', true],
            ['/lastcallmedia\.com/', 'http://google.com', false],
            [['/lastcallmedia\.com/'], 'http://google.com', false],
        ];
    }

    /**
     * @dataProvider hostMatchesTests
     */
    public function testHostMatches($patterns, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::hostMatches($patterns);
        $this->assertEquals($expected, $m($uri));
    }

    public function portIsTests()
    {
        return [
            [null, 'http://test.com', true],
            [null, 'http://test.com:80', true],
            [8081, 'http://test.com:8081', true],
            [8081, 'http://test.com', false],
            [[null, 80], 'http://test.com', true],
        ];
    }

    /**
     * @dataProvider portIsTests
     */
    public function testPortIs($ports, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::portIs($ports);
        $this->assertEquals($expected, $m($uri));
    }

    public function portInTests()
    {
        return [
            [80, 80, 'http://test.com', false],
            [8000, 9000, 'http://test.com:8000', true],
            [8000, 9000, 'http://test.com:9000', true],
            [8000, 9000, 'http://test.com:7999', false],
        ];
    }

    /**
     * @dataProvider portInTests
     */
    public function testPortIn($min, $max, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::portIn($min, $max);
        $this->assertEquals($expected, $m($uri));
    }

    public function pathIsTests()
    {
        return [
            ['', 'http://test.cm', true],
            ['/foo', 'http://test.com/foo', true],
            ['', 'http://test.com/foo', false],
            [['', '/foo'], 'http://test.com/foo', true],
        ];
    }

    /**
     * @dataProvider pathIsTests
     */
    public function testPathIs($paths, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::pathIs($paths);
        $this->assertEquals($expected, $m($uri));
    }

    public function pathMatchesTests() {
        return [
            ['//', 'http://test.com', true],
            ['/foo/', 'foo', true],
            ['/bar/', 'foo', false],
            [['/bar/', '/foo/'], 'foo', true],
            [['!/bar!'], '/bar', true],
            [['!/car!'], '/bar', false],
            [['!/baz!'], '/bar', false],
        ];
    }

    /**
     * @dataProvider pathMatchesTests
     */
    public function testPathMatches($patterns, $uri, $expected) {
        $uri = new Uri($uri);
        $m = MatcherAssert::pathMatches($patterns);
        $this->assertEquals($expected, $m($uri));
    }

    public function pathExtensionIsTests()
    {
        return [
            ['', 'http://test.com', true],
            ['html', 'http://test.com/index.html', true],
            ['php', 'http://test.com/index.html', false],
            [['', 'php'], 'http://test.com/index.php', true],
        ];
    }

    /**
     * @dataProvider pathExtensionIsTests
     */
    public function testPathExtensionIs($exts, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::pathExtensionIs($exts);
        $this->assertEquals($expected, $m($uri));
    }

    public function queryIsTests()
    {
        return [
            ['', 'foo', true],
            ['bar', 'foo?bar', true],
            ['baz', 'foo?bar', false],
            [['bar'], 'foo?bar', true],
        ];
    }

    /**
     * @dataProvider queryIsTests
     */
    public function testQueryIs($queries, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::queryIs($queries);
        $this->assertEquals($expected, $m($uri));
    }

    public function queryMatchesTests()
    {
        return [
            ['/^$/', 'foo', true],
            ['/bar/', 'foo?bar', true],
            ['/baz/', 'foo?bar', false],
            [['/bar/'], 'foo?bar', true],
        ];
    }

    /**
     * @dataProvider queryMatchesTests
     */
    public function testQueryMatches($queries, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::queryMatches($queries);
        $this->assertEquals($expected, $m($uri));
    }

    public function fragmentIsTests()
    {
        return [
            ['', 'http://foo.com', true],
            ['foo', 'http://foo.com#foo', true],
            ['foo', 'http://foo.com#bar', false],
            [['baz'], 'http://foo.com#baz', true],
        ];
    }

    /**
     * @dataProvider fragmentIsTests
     */
    public function testFragmentIs($fragments, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::fragmentIs($fragments);
        $this->assertEquals($expected, $m($uri));
    }

    public function fragmentMatchesTests()
    {
        return [
            ['/^$/', 'foo', true],
            ['/bar/', 'foo#bar', true],
            ['/baz/', 'foo#bar', false],
            [['/bar/'], 'foo#bar', true],
        ];
    }

    /**
     * @dataProvider fragmentMatchesTests
     */
    public function testFragmentMatches($patterns, $uri, $expected)
    {
        $uri = new Uri($uri);
        $m = MatcherAssert::fragmentMatches($patterns);
        $this->assertEquals($expected, $m($uri));
    }
}
