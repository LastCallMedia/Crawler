<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Defines an object that performs transformations on a URI so it is consistent
 * with other versions of the same URI.
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
