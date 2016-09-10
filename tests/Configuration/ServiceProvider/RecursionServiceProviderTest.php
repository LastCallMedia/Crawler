<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Configuration\ServiceProvider\RecursionServiceProvider;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;

class RecursionServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    private function createContainer()
    {
        $container = new Container();
        $container['matcher.internal_html'] = $container->protect(Matcher::all());
        $container['normalizer'] = new Normalizer();
        $container['processors'] = function () {
            return [];
        };
        $container['subscribers'] = function () {
            return [];
        };

        return $container;
    }

    public function testAddsLinkProcessor()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new LinkProcessor($container['matcher.internal_html'], $container['normalizer']);
        $this->assertEquals(['link' => $expected], $container['processors']);
    }

    public function testAddsRedirectSubscriber()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());

        $expected = new RedirectDiscoverer($container['matcher.internal_html'], $container['normalizer']);
        $this->assertEquals(['redirect' => $expected], $container['subscribers']);
    }

    public function testAddsRequestFactory()
    {
        $container = $this->createContainer();
        $container->register(new RecursionServiceProvider());
        $this->assertTrue(is_callable($container['recursion.request_factory']));
        $factory = $container['recursion.request_factory'];
        $request = $factory(new Uri('https://lastcallmedia.com'));
        $this->assertEquals(new Request('GET', 'https://lastcallmedia.com'), $request);
    }
}
