<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use LastCall\Crawler\Configuration\ServiceProvider\FragmentServiceProvider;
use LastCall\Crawler\Fragment\Parser\CSSSelectorParser;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;

class FragmentServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultFragmentHandler()
    {
        $container = new Container();
        $container['subscribers'] = function () {
            return [];
        };
        $container->register(new FragmentServiceProvider());

        $matcher = Matcher::all();
        $normalizer = new Normalizer();

        $container['html_matcher'] = $container->protect($matcher);
        $container['normalizer'] = $normalizer;
        $parsers = [
            'xpath' => new XPathParser(),
            'css' => new CSSSelectorParser(),
        ];
        $processors = [
            'link' => new LinkProcessor($matcher, $normalizer),
        ];
        $expected = [
            'fragment' => new FragmentHandler($parsers, $processors),
        ];

        $this->assertEquals($expected, $container['subscribers']);
    }

    public function testGetFragmentHandlerWithCustomParsersAndProcessors()
    {
        $container = new Container();
        $container['subscribers'] = function () {
            return [];
        };
        $container->register(new FragmentServiceProvider());

        $matcher = Matcher::all();
        $normalizer = new Normalizer();

        $container['html_matcher'] = $container->protect($matcher);
        $container['normalizer'] = $normalizer;
        $container['parsers'] = $parsers = [
            'xpath' => new XPathParser(),
        ];
        $container['processors'] = $processors = [
        ];

        $expected = [
            'fragment' => new FragmentHandler($parsers, $processors),
        ];

        $this->assertEquals($expected, $container['subscribers']);
    }
}
