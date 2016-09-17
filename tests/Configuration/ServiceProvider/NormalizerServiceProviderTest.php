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
            Normalizations::lowercaseHostname(),
            Normalizations::capitalizeEscaped(),
            Normalizations::decodeUnreserved(),
            Normalizations::dropFragment(),
        ]);
        $this->assertEquals($expected, $container['normalizer']);
    }
}
