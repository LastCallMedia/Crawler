<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

interface NormalizerInterface
{
    public function normalize(UriInterface $uri);
}
