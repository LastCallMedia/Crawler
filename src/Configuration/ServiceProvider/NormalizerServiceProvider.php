<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides URL normalization services for the crawler.
 */
class NormalizerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['normalizer'] = function () use ($pimple) {
            return new Normalizer($pimple['normalizations']);
        };
        $pimple['normalizations'] = function () {
            return [
                'lowercaseHostname' => Normalizations::lowercaseHostname(),
                'capitalizeEscaped' => Normalizations::capitalizeEscaped(),
                'decodeUnreserved' => Normalizations::decodeUnreserved(),
                'dropFragment' => Normalizations::dropFragment(),
            ];
        };
    }
}
