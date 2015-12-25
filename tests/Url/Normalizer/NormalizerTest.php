<?php

namespace LastCall\Crawler\Test\Url\Normalizer;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Normalizer;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function getTrailingSlashTests()
    {
        return [
            ['http://google.com', 'http://google.com'],
            ['http://google.com/', 'http://google.com'],
        ];
    }

    public function testReturnsUriObject()
    {
        $normalizer = new Normalizer();
        $normal = $normalizer(new Uri('http://foo.com'));
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $normal);
        $this->assertEquals('http://foo.com', (string) $normal);
    }

    public function testCallsNormalizers()
    {
        $success = false;
        $normalizer = new Normalizer([
            function () use (&$success) {
                $success = true;
            },
        ]);

        $normalizer(new Uri('http://foo.com'));
        $this->assertTrue($success);
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
        return [
            [
                'http://google.com/index.html',
                'http://google.com/index.html',
            ],
            [
                'http://google.com/index.html#foo',
                'http://google.com/index.html',
            ],
        ];
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
        return [
            ['http://google.com', 'http://google.com'],
            ['https://google.com', 'http://google.com'],
        ];
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
        return [
            ['http://google.com/index.html', 'http://google.com/'],
            ['http://google.com/', 'http://google.com/'],
            ['http://google.com/foo', 'http://google.com/foo'],
        ];
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
        return [
            [
                'http://google.com',
                'http://www.google.com',
                ['google.com' => 'www.google.com'],
            ],
            [
                'http://www.google.com',
                'http://www.google.com',
                ['google.com' => 'www.google.com'],
            ],
            [
                'http://alta-vista.com',
                'http://alta-vista.com',
                ['google.com' => 'www.google.com'],
            ],
        ];
    }

    /**
     * @dataProvider getPreferredDomainTests
     */
    public function testPreferredDomainMap($url, $expected, $map)
    {
        $pass = Normalizer::preferredDomainMap($map);
        $this->assertUrlEquals($expected, $pass(new Uri($url)));
    }

    public function getNormalizeCaseTests()
    {
        return [
            [
                'http://google.com',
                'http://google.com',
                'http://GOOGLE.COM',
            ],
            [
                'http://google.com/foo_bar',
                'http://google.com/foo_bar',
                'http://GOOGLE.COM/FOO_BAR',
            ],
            [
                'httP://Google.com/FOo',
                'http://google.com/foo',
                'http://GOOGLE.COM/FOO',
            ],
            [
                'http://google.com/indEx.html',
                'http://google.com/index.html',
                'http://GOOGLE.COM/INDEX.HTML',
            ],
        ];
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
     * @expectedExceptionMessage Invalid case 'foo'
     */
    public function testNormalizeCaseInvalid()
    {
        Normalizer::normalizeCase('foo');
    }

    protected function assertUrlEquals($expected, $url)
    {
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $url);
        $this->assertEquals($expected, (string) $url);
    }
}
