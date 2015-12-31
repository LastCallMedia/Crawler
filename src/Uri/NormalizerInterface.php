<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Modifies a URI so it follows a standard format.
 */
interface NormalizerInterface
{
    /**
     * Normalize a URI.
     *
     * @param \Psr\Http\Message\UriInterface $uri The URI to normalize
     *
     * @return \Psr\Http\Message\UriInterface The normalized URI
     */
    public function normalize(UriInterface $uri);
}
