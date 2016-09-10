<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use LastCall\Crawler\Configuration\ServiceProvider\FragmentServiceProvider;
use LastCall\Crawler\Fragment\Parser\CSSSelectorParser;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use LastCall\Crawler\Fragment\Processor\FragmentProcessorInterface;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
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

        $parsers = [
            'xpath' => new XPathParser(),
            'css' => new CSSSelectorParser(),
        ];

        $expected = [
            'fragment' => new FragmentHandler($parsers, []),
        ];

        $this->assertEquals($expected, $container['subscribers']);
    }

    public function testOverrideProcessorsAndParsers()
    {
        $container = new Container();
        $container['subscribers'] = function () {
            return [];
        };
        $processor = $this->prophesize(FragmentProcessorInterface::class);
        $processor->getSubscribedMethods()->willReturn([]);

        $container->register(new FragmentServiceProvider());
        $container['processors'] = $processors = [$processor->reveal()];
        $container['parsers'] = $parsers = [];
        $expected = [
            'fragment' => new FragmentHandler($parsers, $processors),
        ];
        $this->assertEquals($expected, $container['subscribers']);
    }
}
