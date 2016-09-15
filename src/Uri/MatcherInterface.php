<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Defines a URI matcher.
 */
interface MatcherInterface
{
    /**
     * Determine whether the URI matches the conditions.
     *
     * @param \Psr\Http\Message\UriInterface $uri
     *
     * @return mixed
     */
    public function matches(UriInterface $uri);
}
