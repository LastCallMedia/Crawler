<?php

namespace LastCall\Crawler\Test\Url\Normalizer;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Normalizer;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    protected function assertUrlEquals($expected, $url)
    {
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $url);
        $this->assertEquals($expected, (string) $url);
    }

    public function getTrailingSlashTests()
    {
        return [
            ['http://google.com', 'http://google.com'],
            ['http://google.com/', 'http://google.com'],
        ];
    }

    public function testReturnsUriObject()
    {
        $uri = new Uri('http://foo.com');
        $normalizer = new Normalizer();
        $this->assertUrlEquals('http://foo.com', $normalizer($uri));
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

    public function lowercaseSchemeAndHostTests()
    {
        return [
            ['HTTP://GOOGLE.COM/FOO', 'http://google.com/FOO'],
            // @todo: UTF-8 hostnames are broken in Guzzle.
            // Fix there first.
            //['http://مثال.إختبار', 'http://مثال.إختبار'],
        ];
    }

    /**
     * @dataProvider lowercaseSchemeAndHostTests
     */
    public function testLowercaseSchemeAndHost($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::lowercaseSchemeAndHost();
        $this->assertEquals($expected, (string) $normalizer($uri));
    }

    public function capitalizeEscapedTests()
    {
        return [
            ['http://foo%3a.com/%3a?bar%3a', 'http://foo%3A.com/%3A?bar%3A'],
        ];
    }

    /**
     * @dataProvider capitalizeEscapedTests
     */
    public function testCapitalizeEscaped($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::capitalizeEscaped();
        $this->assertEquals($expected, (string) $normalizer($uri));
    }

    public function decodeUnreservedTests()
    {
        return [
            ['http://foo%2Dbar.com/bar%2Dbaz?baz%5Fbar#%31', 'http://foo-bar.com/bar-baz?baz_bar#1'],
        ];
    }

    /**
     * @dataProvider decodeUnreservedTests
     */
    public function testDecodeUnreserved($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::decodeUnreserved();
        $this->assertEquals($expected, (string) $normalizer($uri));
    }

    public function addTrailingSlashTests()
    {
        return [
            ['/', '/'],
            ['/foo', '/foo/'],
        ];
    }

    /**
     * @dataProvider addTrailingSlashTests
     */
    public function testAddTrailingSlash($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::addTrailingSlash();
        $this->assertUrlEquals($expected, $normalizer($uri));
    }

    public function dropIndexTests()
    {
        return [
            ['/', '/'],
            ['/index.html', '/'],
            ['index.html', ''],
            ['index.cfm', ''],
            ['default.html', ''],
        ];
    }

    /**
     * @dataProvider dropIndexTests
     */
    public function testDropIndex($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::dropIndex();
        $this->assertUrlEquals($expected, $normalizer($uri));
    }

    public function dropFragmentTests()
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
     * @dataProvider dropFragmentTests
     */
    public function testDropFragment($urlString, $expected)
    {
        $uri = new Uri($urlString);
        $handler = Normalizer::dropFragment();
        $this->assertUrlEquals($expected, $handler($uri));
    }

    public function rewriteSchemeTests()
    {
        return [
            ['', ''],
            ['http://foo', 'https://foo'],
            ['https://foo', 'https://foo'],
        ];
    }

    /**
     * @dataProvider rewriteSchemeTests
     */
    public function testRewriteScheme($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::rewriteScheme(['http' => 'https']);
        $this->assertUrlEquals($expected, $normalizer($uri));
    }

    public function rewriteHostTests()
    {
        return [
            ['', ''],
            ['http://foo.com', 'http://www.foo.com'],
            ['http://www.foo.com', 'http://www.foo.com'],
        ];
    }

    /**
     * @dataProvider rewriteHostTests
     */
    public function testrewriteHost($uriString, $expected)
    {
        $uri = new Uri($uriString);
        $normalizer = Normalizer::rewriteHost([
            'foo.com' => 'www.foo.com',
        ]);
        $this->assertUrlEquals($expected, $normalizer($uri));
    }
}
