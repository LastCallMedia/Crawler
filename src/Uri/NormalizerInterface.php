<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

interface NormalizerInterface
{
    /**
     * Determine whether a given URL matches the conditions.
     *
     * @param \Psr\Http\Message\UriInterface $uri
     *
     * @return bool
     */
    public function normalize(UriInterface $uri);
}
