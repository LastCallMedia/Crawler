<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Configuration\ServiceProvider\MatcherServiceProvider;
use LastCall\Crawler\Uri\MatcherInterface;
use Pimple\Container;

class MatcherServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testAddsMatchers()
    {
        // Unfortunately, we can't do much to test that matchers are configured
        // properly, since matchers can't be compared.
        $container = new Container();
        $container->register(new MatcherServiceProvider(), [
            'base_url' => 'https://lastcallmedia.com/foo',
        ]);

        $matchers = [
            'matcher.internal',
            'matcher.html',
            'matcher.asset',
            'matcher.internal_html',
            'matcher.internal_asset',
        ];

        foreach ($matchers as $matcher) {
            $this->assertTrue(is_a($container[$matcher], MatcherInterface::class));
        }
    }

    public function getMatcherTests()
    {
        return [
            // Internal matcher
            ['matcher.internal', true, 'https://lastcallmedia.com/foo'],
            ['matcher.internal', true, 'https://lastcallmedia.com/foo/bar'],
            ['matcher.internal', false, 'http://lastcallmedia.com/foo'],
            ['matcher.internal', false, 'https://lastcallmedia.com'],
            ['matcher.internal', false, 'https://lastcallmedia.com/bar/foo'],

            // HTML Matcher
            ['matcher.html', true, '/test'],
            ['matcher.html', true, '/test.html'],
            ['matcher.html', false, '/test.css'],

            // Asset matcher
            ['matcher.asset', true, '/test.css'],
            ['matcher.asset', true, '/test.jpg'],
            ['matcher.asset', false, '/test'],

            // Internal HTML matcher
            ['matcher.internal_html', true, 'https://lastcallmedia.com/foo/test.html'],
            ['matcher.internal_html', false, 'https://lastcallmedia.com/foo/test.css'],
            ['matcher.internal_html', false, 'https://google.com/foo/test.html'],

            // Internal asset matcher.
            ['matcher.internal_asset', true, 'https://lastcallmedia.com/foo/test.css'],
            ['matcher.internal_asset', false, 'https://lastcallmedia.com/foo/test.html'],
            ['matcher.internal_asset', false, 'https://lastcallmedia.com/test.css'],
        ];
    }

    /**
     * @dataProvider getMatcherTests
     */
    public function testMatcher($matcher, $expected, $url)
    {
        $container = new Container();
        $container->register(new MatcherServiceProvider(), [
            'base_url' => 'https://lastcallmedia.com/foo',
        ]);
        $matches = $container[$matcher]->matches(new Uri($url));
        $this->assertEquals($expected, $matches);
    }
}
