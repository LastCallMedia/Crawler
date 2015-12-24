<?php

namespace LastCall\Crawler\Test\Url;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\TraceableUri;

class TraceableUriTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPreviousForms()
    {
        $uri = new TraceableUri(new Uri('http://google.com/index.html#foo'));
        $uri = $uri->withPath('')->withFragment(false);
        $this->assertEquals('http://google.com', (string) $uri);
        $this->assertEquals('http://google.com#foo',
            (string) $uri->getPrevious());
        $this->assertEquals('http://google.com/index.html#foo',
            (string) $uri->getPrevious()->getPrevious());
        $this->assertEquals(null,
            $uri->getPrevious()->getPrevious()->getPrevious());
    }

    public function testGetPreviousFormsWithNoPreviousForms()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals(null, $uri->getPrevious());
    }

    public function testSetsNextForm()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $altered = $uri->withPath('foo');
        $this->assertEquals('http://google.com', $altered->getPrevious());
        $this->assertEquals('http://google.com/foo',
            $altered->getPrevious()->getNext());
    }

    public function testGetScheme()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http', $uri->getScheme());
    }

    public function testGetAuthority()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('google.com', $uri->getAuthority());
    }

    public function testGetUserInfo()
    {
        $uri = new TraceableUri(new Uri('http://foo:bar@google.com'));
        $this->assertEquals('foo:bar', $uri->getUserInfo());
    }

    public function testGetPath()
    {
        $uri = new TraceableUri(new Uri('http://google.com/baz'));
        $this->assertEquals('/baz', $uri->getPath());
    }

    public function testGetQuery()
    {
        $uri = new TraceableUri(new Uri('http://google.com?foo'));
        $this->assertEquals('foo', $uri->getQuery());
    }

    public function testGetFragment()
    {
        $uri = new TraceableUri(new Uri('http://google.com#foo'));
        $this->assertEquals('foo', $uri->getFragment());
    }

    public function testWithScheme()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('https://google.com', $uri->withScheme('https'));
    }

    public function testWithUserInfo()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http://foo:bar@google.com',
            $uri->withUserInfo('foo:bar'));
    }

    public function testWithHost()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http://alta-vista.com',
            $uri->withHost('alta-vista.com'));
    }

    public function testWithPort()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http://google.com:98', $uri->withPort(98));
    }

    public function testWithPath()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http://google.com/foo', $uri->withPath('foo'));
    }

    public function testWithQuery()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http://google.com?foo=bar',
            $uri->withQuery('foo=bar'));
    }

    public function testWithFragment()
    {
        $uri = new TraceableUri(new Uri('http://google.com'));
        $this->assertEquals('http://google.com#bar', $uri->withFragment('bar'));
    }
}
