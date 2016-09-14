<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Configuration\ServiceProvider\RecursionServiceProvider;
use LastCall\Crawler\Handler\Discovery\AssetDiscoverer;
use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Handler\Uri\UriRecursor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;

class RecursionServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    private function createContainer()
    {
        $container = new Container();
        $container['matcher.internal_html'] = $container->protect(Matcher::all());
        $container['matcher.internal_asset'] = $container->protect(Matcher::all());
        $container['normalizer'] = new Normalizer();
        $container['processors'] = function () {
            return [];
        };
        $container['subscribers'] = function () {
            return [];
        };

        return $container;
    }

    public function testAddsRedirectDiscoverer()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new RedirectDiscoverer($container['normalizer']);
        $this->assertEquals($expected, $container['subscribers']['discovery.redirect']);
    }

    public function testAddsAssetDiscoverer()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new AssetDiscoverer($container['normalizer']);
        $this->assertEquals($expected, $container['subscribers']['discovery.asset']);
    }

    public function testAddsLinkDiscoverer()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new LinkDiscoverer($container['normalizer']);
        $this->assertEquals($expected, $container['subscribers']['discovery.link']);
    }

    public function testAddsInternalHtmlUriRecursor()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new UriRecursor($container['matcher.internal_html'], $container['request_factory.internal_html']);
        $this->assertEquals($expected, $container['subscribers']['uri_recursor.internal_html']);
    }

    public function testAddsInternalAssetUriRecursor()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new UriRecursor($container['matcher.internal_asset'], $container['request_factory.internal_asset']);
        $this->assertEquals($expected, $container['subscribers']['uri_recursor.internal_asset']);
    }

    public function testAddsRequestFactories()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());
        $this->assertTrue(is_callable($container['request_factory.internal_html']));
        $this->assertTrue(is_callable($container['request_factory.internal_asset']));
        $uri = new Uri('http://google.com');
        $request = call_user_func($container['request_factory.internal_html'], $uri);
        $this->assertEquals(new Request('GET', $uri), $request);
        $request = call_user_func($container['request_factory.internal_asset'], $uri);
        $this->assertEquals(new Request('HEAD', $uri), $request);
    }
}
