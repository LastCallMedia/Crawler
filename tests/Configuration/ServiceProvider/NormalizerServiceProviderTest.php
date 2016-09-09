<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use LastCall\Crawler\Configuration\ServiceProvider\NormalizerServiceProvider;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;

class NormalizerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultNormalizer()
    {
        $container = new Container();
        $container->register(new NormalizerServiceProvider());

        $expected = new Normalizer([
            'lowercaseHostname' => Normalizations::lowercaseHostname(),
            'capitalizeEscaped' => Normalizations::capitalizeEscaped(),
            'decodeUnreserved' => Normalizations::decodeUnreserved(),
            'dropFragment' => Normalizations::dropFragment(),
        ]);
        $this->assertEquals($expected, $container['normalizer']);
    }

    public function testOverrideNormalizations()
    {
        $container = new Container();
        $container->register(new NormalizerServiceProvider());

        $fn = function () {
        };
        $container['normalizations'] = ['foo' => $fn];
        $expected = new Normalizer([
            'foo' => $fn,
        ]);
        $this->assertEquals($expected, $container['normalizer']);
    }
}
