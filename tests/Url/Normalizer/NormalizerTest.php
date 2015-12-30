<?php

namespace LastCall\Crawler\Test\Url\Normalizer;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Psr\Http\Message\UriInterface;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    protected function assertUrlEquals($expected, $url)
    {
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $url);
        $this->assertEquals($expected, (string) $url);
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
     * Assert that the normalizer runs until a run returns the exact same url as
     * the previous run.
     */
    public function testReinvokesNormalizers()
    {
        $calls = 0;
        $normalizer = new Normalizer([
            Normalizations::rewriteHost(['www.foo.com' => 'www2.foo.com']),
            Normalizations::rewriteHost(['foo.com' => 'www.foo.com']),
            function ($uri) use (&$calls) {
                ++$calls;

                return $uri;
            },
        ]);
        $uri = new Uri('http://foo.com');
        $this->assertUrlEquals('http://www2.foo.com', $normalizer($uri));
        $this->assertEquals(3, $calls);
    }

    public function testDoesNotNormalizeUnmatchedUrls()
    {
        $matcher = Matcher::all()->never();
        $normalizer = new Normalizer([
            function (UriInterface $uri) {
                $this->fail('Normalizers were called when the matcher returned false.');
            },
        ], $matcher);
        $uri = new Uri('http://foo.com');
        $normalizer($uri);
    }

    public function testNormalizesMatchedUrls()
    {
        $calls = 0;
        $matcher = Matcher::all()->always();
        $normalizer = new Normalizer([
           function (UriInterface $uri) use (&$calls) {
               ++$calls;

               return $uri;
           },
        ], $matcher);
        $uri = new Uri('http://foo.com');
        $normalizer($uri);
        $this->assertEquals(1, $calls);
    }
}
