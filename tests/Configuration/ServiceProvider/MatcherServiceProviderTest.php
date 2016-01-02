<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use LastCall\Crawler\Configuration\ServiceProvider\MatcherServiceProvider;
use LastCall\Crawler\Uri\Matcher;
use Pimple\Container;

class MatcherServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testAddsMatcher()
    {
        $container = new Container();
        $container->register(new MatcherServiceProvider(), [
            'base_url' => 'https://lastcallmedia.com',
        ]);

        $expected = Matcher::all()
            ->schemeIs(['http', 'https'])
            ->hostIs('lastcallmedia.com');

        $this->assertEquals($expected, $container['matcher']);
    }

    public function testHasHtmlMatcher()
    {
        $container = new Container();
        $container->register(new MatcherServiceProvider(), [
            'base_url' => 'https://lastcallmedia.com',
        ]);

        $expected = Matcher::all()
            ->schemeIs(['http', 'https'])
            ->hostIs('lastcallmedia.com')
            ->pathExtensionIs(['', 'html', 'htm', 'php', 'asp', 'aspx', 'cfm']);

        $this->assertEquals($expected, $container['html_matcher']);
    }

    public function testCanOverrideHtmlMatches()
    {
        $container = new Container();
        $container->register(new MatcherServiceProvider(), [
            'base_url' => 'https://lastcallmedia.com',
            'html_extensions' => ['foo', 'bar'],
        ]);

        $expected = Matcher::all()
            ->schemeIs(['http', 'https'])
            ->hostIs('lastcallmedia.com')
            ->pathExtensionIs(['foo', 'bar']);

        $this->assertEquals($expected, $container['html_matcher']);
    }
}
