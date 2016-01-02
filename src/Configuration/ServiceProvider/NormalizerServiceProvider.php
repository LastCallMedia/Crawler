<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class NormalizerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['normalizer'] = function () use ($pimple) {
            $matcher = null;
            if (isset($pimple['normalizer_matcher'])) {
                $matcher = $pimple['normalizer_matcher'];
            }

            return new Normalizer($pimple['normalizations'], $matcher);
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
