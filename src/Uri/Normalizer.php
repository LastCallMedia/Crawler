<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Normalize URLs to a consistent state.
 *
 * @see https://tools.ietf.org/html/rfc3986
 */
class Normalizer
{
    private $handlers = [];

    public function __construct(array $handlers = [], callable $matcher = null)
    {
        $this->handlers = $handlers;
        $this->matcher = $matcher;
    }

    public function __invoke(UriInterface $uri)
    {
        if ($this->matcher) {
            $matcher = $this->matcher;
            if (!$matcher($uri)) {
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
