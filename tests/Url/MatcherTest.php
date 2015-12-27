<?php

namespace LastCall\Crawler\Test\Url;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Matcher;

class MatcherTest extends \PHPUnit_Framework_TestCase
{
    private function uri($string) {
        return new Uri($string);
    }
    public function testAllReturnsTrueWhenAllReturnTrue()
    {
        $matcher = Matcher::create()->all();
        $matcher->always();
        $this->assertTrue($matcher(new Uri('foo')));
    }

    public function testAllReturnsFalseWhenAnyReturnFalse()
    {
        $matcher = Matcher::create()->all();
        $matcher->always()->never();
        $this->assertFalse($matcher(new Uri('foo')));
    }

    public function testAnyReturnsTrueWhenAnyReturnTrue()
    {
        $matcher = Matcher::create()->any();
        $matcher->always()->never();
        $this->assertTrue($matcher(new Uri('foo')));
    }

    public function testAnyReturnsFalseWhenNoneReturnTrue()
    {
        $matcher = Matcher::create()->any();
        $matcher->never()->never();
        $this->assertFalse($matcher(new Uri('foo')));
    }

    public function testAlways()
    {
        $matcher = Matcher::create();
        $this->assertSame($matcher, $matcher->always());
        $this->assertTrue($matcher(new Uri('foo')));
    }

    public function testNever()
    {
        $matcher = Matcher::create();
        $this->assertSame($matcher, $matcher->never());
        $this->assertFalse($matcher(new Uri('foo')));
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
    public function testSchemeIs($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->schemeIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testSchemeMatches($assertArgs, $uriString, $expected) {
        $matcher = Matcher::create()->schemeMatches($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testHostIs($assertArgs, $uriString, $expected) {
        $matcher = Matcher::create()->hostIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testHostMatches($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->hostMatches($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testPortIs($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->portIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testPortIn($min, $max, $uriString, $expected)
    {
        $matcher = Matcher::create()->portIn($min, $max);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testPathIs($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->pathIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
    }

    public function pathMatchesTests()
    {
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
    public function testPathMatches($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->pathMatches($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testPathExtensionIs($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->pathExtensionIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testQueryIs($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->queryIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testQueryMatches($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->queryMatches($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testFragmentIs($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->fragmentIs($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
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
    public function testFragmentMatches($assertArgs, $uriString, $expected)
    {
        $matcher = Matcher::create()->fragmentMatches($assertArgs);
        $this->assertEquals($expected, $matcher($this->uri($uriString)));
    }
}
