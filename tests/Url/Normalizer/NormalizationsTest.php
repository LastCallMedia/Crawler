<?php

namespace LastCall\Crawler\Test\Url\Normalizer;

use LastCall\Crawler\Uri\Normalizations;
use GuzzleHttp\Psr7\Uri;

class NormalizationsTest extends \PHPUnit_Framework_TestCase
{
    private function uri($uriString) {
        return new Uri($uriString);
    }

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

    /**
     * @dataProvider getTrailingSlashTests
     */
    public function testStripTrailingSlash($uriString, $expected)
    {
        $uri = $this->uri($uriString);
        $handler = Normalizations::stripTrailingSlash();
        $this->assertUrlEquals($expected, $handler($uri));
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::lowercaseHostname();
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::capitalizeEscaped();
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::decodeUnreserved();
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::addTrailingSlash();
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::dropIndex();
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
        $uri = $this->uri($urlString);
        $handler = Normalizations::dropFragment();
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::rewriteScheme(['http' => 'https']);
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
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::rewriteHost([
            'foo.com' => 'www.foo.com',
        ]);
        $this->assertUrlEquals($expected, $normalizer($uri));
    }

    public function sortQueryTests()
    {
        return [
            ['', ''],
            ['foo?bar', 'foo?bar'],
            ['foo?baz&bar', 'foo?bar&baz'],
        ];
    }

    /**
     * @dataProvider sortQueryTests
     */
    public function testSortQuery($uriString, $expected)
    {
        $uri = $this->uri($uriString);
        $normalizer = Normalizations::sortQuery();
        $this->assertUrlEquals($expected, $normalizer($uri));
    }
}
