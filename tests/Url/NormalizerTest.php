<?php

namespace LastCall\Crawler\Test\Url;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Url\ArrayMap;
use LastCall\Crawler\Url\MapNormalizerPass;
use LastCall\Crawler\Url\Normalizer;
use LastCall\Crawler\Url\StripFragmentNormalizerPass;
use LastCall\Crawler\Url\StripSSLNormalizerPass;
use LastCall\Crawler\Url\StripTrailingSlashNormalizerPass;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{

    public function getTrailingSlashTests()
    {
        return array(
            array('http://google.com', 'http://google.com'),
            array('http://google.com/', 'http://google.com')
        );
    }

    public function testNormalizeReturnsUriObject()
    {
        $normalizer = new Normalizer();
        $normal = $normalizer->normalize('http://foo.com');
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $normal);
    }

    public function testCallsNormalizers()
    {
        $normalizer = new Normalizer();
        $success = 0;
        $normalizer->push(function () use (&$success) {
            $success++;
        });
        $normalizer->normalize('http://foo.com');
        $this->assertEquals(1, $success);
    }

    /**
     * @dataProvider getTrailingSlashTests
     */
    public function testStripTrailingSlash($url, $expected)
    {
        $handler = Normalizer::stripTrailingSlash();
        $this->assertUrlEquals($expected, $handler(new Uri($url)));
    }

    public function getStripFragmentTests()
    {
        return array(
            array(
                'http://google.com/index.html',
                'http://google.com/index.html'
            ),
            array(
                'http://google.com/index.html#foo',
                'http://google.com/index.html'
            ),
        );
    }

    /**
     * @dataProvider getStripFragmentTests
     */
    public function testStripFragment($url, $expected)
    {
        $handler = Normalizer::stripFragment();
        $this->assertUrlEquals($expected, $handler(new Uri($url)));
    }

    public function getStripSSLTests()
    {
        return array(
            array('http://google.com', 'http://google.com'),
            array('https://google.com', 'http://google.com'),
        );
    }

    /**
     * @dataProvider getStripSSLTests
     */
    public function testStripSSL($url, $expected)
    {
        $handler = Normalizer::stripSSL();
        $this->assertUrlEquals($expected, $handler(new Uri($url)));
    }

    public function getStripIndexTests()
    {
        return array(
            array('http://google.com/index.html', 'http://google.com/'),
            array('http://google.com/', 'http://google.com/'),
            array('http://google.com/foo', 'http://google.com/foo'),
        );
    }

    /**
     * @dataProvider getStripIndexTests
     */
    public function testStripIndex($url, $expected)
    {
        $handler = Normalizer::stripIndex();
        $this->assertUrlEquals($expected, $handler(new Uri($url)));
    }

    public function getPreferredDomainTests()
    {
        return array(
            array(
                'http://google.com',
                'http://www.google.com',
                array('google.com' => 'www.google.com')
            ),
            array(
                'http://www.google.com',
                'http://www.google.com',
                array('google.com' => 'www.google.com')
            ),
            array(
                'http://alta-vista.com',
                'http://alta-vista.com',
                array('google.com' => 'www.google.com')
            ),
        );
    }

    /**
     * @dataProvider getPreferredDomainTests
     */
    public function testPreferredDomainMap($url, $expected, $map)
    {
        $pass = Normalizer::preferredDomainMap($map);
        $this->assertUrlEquals($expected, $pass(new Uri($url)));
    }

    public function testPassUri()
    {
        $pass = new Normalizer();
        $this->assertUrlEquals('http://google.com',
            $pass->normalize(new Uri('http://google.com')));
    }

    public function getNormalizeCaseTests()
    {
        return array(
            array(
                'http://google.com',
                'http://google.com',
                'http://google.com'
            ),
            array(
                'http://google.com/FOO',
                'http://google.com/foo',
                'http://google.com/FOO'
            ),
            array(
                'http://google.com/indEx.html',
                'http://google.com/index.html',
                'http://google.com/INDEX.HTML'
            ),
        );
    }

    /**
     * @dataProvider getNormalizeCaseTests
     */
    public function testNormalizeCase($url, $expectedLower, $expectedUpper)
    {
        $lower = Normalizer::normalizeCase('lower');
        $upper = Normalizer::normalizeCase('upper');
        $url = new Uri($url);
        $this->assertUrlEquals($expectedLower, $lower($url));
        $this->assertUrlEquals($expectedUpper, $upper($url));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNormalizeCaseInvalid()
    {
        Normalizer::normalizeCase('foo');
    }

    protected function assertUrlEquals($expected, $url)
    {
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $url);
        $this->assertEquals($expected, (string)$url);
    }
}