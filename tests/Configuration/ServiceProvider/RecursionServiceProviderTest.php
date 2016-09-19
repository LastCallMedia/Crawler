<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Configuration\ServiceProvider\RecursionServiceProvider;
use LastCall\Crawler\Handler\Discovery\ImageDiscoverer;
use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Handler\Discovery\ScriptDiscoverer;
use LastCall\Crawler\Handler\Discovery\StylesheetDiscoverer;
use LastCall\Crawler\Handler\Uri\UriRecursor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use LastCall\Crawler\Uri\NormalizerInterface;
use Pimple\Container;

class RecursionServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    private function createContainer($values = [])
    {
        $container = new Container();
        $container['matcher.internal'] = $container->protect(Matcher::all());
        $container['matcher.html'] = $container->protect(Matcher::all());
        $container['matcher.asset'] = $container->protect(Matcher::all());
        $container['normalizer'] = new Normalizer();
        $container->register(new RecursionServiceProvider(), $values);

        return $container;
    }

    public function testAddsNormalizers()
    {
        $container = $this->createContainer();
        $this->assertEquals($container['normalizer'], $container['normalizer.link']);
        $this->assertEquals($container['normalizer'], $container['normalizer.redirect']);
        $this->assertEquals($container['normalizer'], $container['normalizer.image']);
        $this->assertEquals($container['normalizer'], $container['normalizer.script']);
        $this->assertEquals($container['normalizer'], $container['normalizer.stylesheet']);
    }

    public function testAddsRedirectDiscoverer()
    {
        $container = $this->createContainer([
            'normalizer.redirect' => $this->prophesize(NormalizerInterface::class)->reveal(),
        ]);
        $expected = new RedirectDiscoverer($container['normalizer.redirect']);
        $this->assertEquals($expected, $container['discoverer.redirect']);
    }

    public function testAddsImageDiscoverer()
    {
        $container = $this->createContainer([
            'normalizer.image' => $this->prophesize(NormalizerInterface::class)->reveal(),
        ]);
        $expected = new ImageDiscoverer($container['normalizer.image']);
        $this->assertEquals($expected, $container['discoverer.image']);
    }

    public function testAddsScriptDiscoverer()
    {
        $container = $this->createContainer([
            'normalizer.script' => $this->prophesize(NormalizerInterface::class)->reveal(),
        ]);
        $expected = new ScriptDiscoverer($container['normalizer.script']);
        $this->assertEquals($expected, $container['discoverer.script']);
    }

    public function testAddsStylesheetDiscoverer()
    {
        $container = $this->createContainer([
            'normalizer.stylesheet' => $this->prophesize(NormalizerInterface::class)->reveal(),
        ]);
        $expected = new StylesheetDiscoverer($container['normalizer.stylesheet']);
        $this->assertEquals($expected, $container['discoverer.stylesheet']);
    }

    public function testAddsLinkDiscoverer()
    {
        $container = $this->createContainer([
            'normalizer.link' => $this->prophesize(NormalizerInterface::class)->reveal(),
        ]);
        $expected = new LinkDiscoverer($container['normalizer.link']);
        $this->assertEquals($expected, $container['discoverer.link']);
    }

    public function testAddsInternalHtmlUriRecursor()
    {
        $container = $this->createContainer();

        $expected = new UriRecursor($container['matcher.internal_html'], $container['request_factory.internal_html']);
        $this->assertEquals($expected, $container['recursor.internal_html']);
    }

    public function testAddsInternalAssetUriRecursor()
    {
        $container = $this->createContainer();

        $expected = new UriRecursor($container['matcher.internal_asset'], $container['request_factory.internal_asset']);
        $this->assertEquals($expected, $container['recursor.internal_asset']);
    }

    public function testAddsInternalHtmlRequestFactory()
    {
        $container = $this->createContainer();

        $this->assertTrue(is_callable($container['request_factory.internal_html']));
        $uri = new Uri('http://google.com');
        $request = call_user_func($container['request_factory.internal_html'], $uri);
        $this->assertEquals(new Request('GET', $uri), $request);
    }

    public function testAddsInternalAssetRequestFactory()
    {
        $container = $this->createContainer();

        $this->assertTrue(is_callable($container['request_factory.internal_asset']));
        $uri = new Uri('http://google.com');
        $request = call_user_func($container['request_factory.internal_asset'], $uri);
        $this->assertEquals(new Request('HEAD', $uri), $request);
    }
}
