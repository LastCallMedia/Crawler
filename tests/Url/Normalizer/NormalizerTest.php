<?php

namespace LastCall\Crawler\Test\Url\Normalizer;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;

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
}
