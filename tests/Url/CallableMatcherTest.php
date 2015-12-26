<?php

namespace LastCall\Crawler\Test\Url;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Matcher;

class CallableMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testAllReturnsTrueWhenAllReturnTrue()
    {
        $matcher = Matcher::create();
        $matcher->always();
        $this->assertTrue($matcher(new Uri('foo')));
    }

    public function testAllReturnsFalseWhenAnyReturnFalse()
    {
        $matcher = Matcher::create();
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

    public function testHostIs()
    {
        $matcher = Matcher::create();
        $this->assertSame($matcher, $matcher->hostIs('foo.com'));
        $this->assertTrue($matcher(new Uri('http://foo.com')));
        $this->assertFalse($matcher(new Uri('http://bar.com')));
    }

    public function testSchemeIs()
    {
        $matcher = Matcher::create();
        $this->assertSame($matcher, $matcher->schemeIs('http'));
        $this->assertTrue($matcher(new Uri('http://foo.com')));
        $this->assertFalse($matcher(new Uri('https://foo.com')));
    }

    public function testPathIs()
    {
        $matcher = Matcher::create();
        $this->assertSame($matcher, $matcher->pathIs('foo'));
        $this->assertTrue($matcher(new Uri('foo')));
        $this->assertFalse($matcher(new Uri('bar')));
    }
}
