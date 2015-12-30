<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

interface MatcherInterface
{
    public function matches(UriInterface $uri);
}
