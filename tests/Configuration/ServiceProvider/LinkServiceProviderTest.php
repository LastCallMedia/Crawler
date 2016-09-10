<?php


namespace LastCall\Crawler\Test\Configuration\ServiceProvider;


use LastCall\Crawler\Configuration\ServiceProvider\LinkServiceProvider;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;

class LinkServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testAddsLinkProcessor() {
        $container = new Container();
        $matcher = Matcher::all();
        $container['matcher.internal_html'] = $container->protect($matcher);
        $container['normalizer'] = $normalizer = new Normalizer();
        $container['processors'] = function () {
            return [];
        };

        $container->register(new LinkServiceProvider());

        $expected = new LinkProcessor($matcher, $normalizer);
        $this->assertEquals(['link' => $expected], $container['processors']);
    }

}
