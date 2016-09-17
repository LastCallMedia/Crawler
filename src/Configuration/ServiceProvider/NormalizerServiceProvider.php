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
        // Transforms URLs into a standard form.
        $pimple['normalizer'] = function () use ($pimple) {
            return new Normalizer([
                Normalizations::lowercaseHostname(),
                Normalizations::capitalizeEscaped(),
                Normalizations::decodeUnreserved(),
                Normalizations::dropFragment(),
            ]);
        };
    }
}
