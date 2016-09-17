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

    public function getNormalizers()
    {
        return [
            [new Normalizer()],
            [$this->getMock(NormalizerInterface::class)],
        ];
    }

    public function testAddsRedirectDiscoverer()
    {
        $container = $this->createContainer();
        $expected = new RedirectDiscoverer($container['normalizer']);
        $this->assertEquals($expected, $container['discoverer.redirect']);
    }

    public function testRedirectDiscovererNormalizerIsOverrideable()
    {
        $normalizer = new Normalizer([function () {
        }]);
        $container = $this->createContainer([
            'normalizer.redirect' => $normalizer,
        ]);
        $expected = new RedirectDiscoverer($normalizer);
        $this->assertEquals($expected, $container['discoverer.redirect']);
    }

    public function testAddsAssetDiscoverer()
    {
        $container = $this->createContainer();

        $expected = new AssetDiscoverer($container['normalizer']);
        $this->assertEquals($expected, $container['discoverer.asset']);
    }

    public function testAssetDiscovererNormalizerIsOverrideable()
    {
        $normalizer = new Normalizer([function () {
        }]);
        $container = $this->createContainer([
            'normalizer.asset' => $normalizer,
        ]);
        $expected = new AssetDiscoverer($normalizer);
        $this->assertEquals($expected, $container['discoverer.asset']);
    }

    public function testAddsLinkDiscoverer()
    {
        $container = $this->createContainer();

        $expected = new LinkDiscoverer($container['normalizer']);
        $this->assertEquals($expected, $container['discoverer.link']);
    }

    public function testLinkDiscovererNormalizerIsOverrideable()
    {
        $normalizer = new Normalizer([function () {
        }]);
        $container = $this->createContainer([
            'normalizer.link' => $normalizer,
        ]);
        $expected = new LinkDiscoverer($normalizer);
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
