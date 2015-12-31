<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Normalize URLs to a consistent state.
 *
 * @see https://tools.ietf.org/html/rfc3986
 */
class Normalizer implements NormalizerInterface
{
    private $handlers = [];

    public function __construct(array $handlers = [], MatcherInterface $matcher = null)
    {
        $this->handlers = $handlers;
        $this->matcher = $matcher;
    }

    public function normalize(UriInterface $uri)
    {
        if ($this->matcher) {
            if (!$this->matcher->matches($uri)) {
                return $uri;
            }
        }

        do {
            $str = (string) $uri;
            foreach ($this->handlers as $handler) {
                $uri = $handler($uri);
            }
            $newStr = (string) $uri;
        }
        // Normalization is achieved once running normalizers causes no changes.
        while ($str !== $newStr);

        return $uri;
    }
}
