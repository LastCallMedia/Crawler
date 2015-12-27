<?php

namespace LastCall\Crawler\Common;

use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Psr\Http\Message\UriInterface;

trait HasResolvingNormalizer
{
    protected function getResolvingNormalizer(UriInterface $uri, callable $normalizer = null)
    {
        $resolver = Normalizations::resolve($uri);
        if ($normalizer) {
            return new Normalizer([
                $resolver,
                $normalizer,
            ]);
        }

        return $resolver;
    }
}
