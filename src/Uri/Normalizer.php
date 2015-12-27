<?php

namespace LastCall\Crawler\Uri;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Normalize URLs to a consistent state.
 *
 * @see https://tools.ietf.org/html/rfc3986
 */
class Normalizer
{
    private $handlers = [];

    public function __construct(array $handlers = [], $traceable = false)
    {
        $this->handlers = $handlers;
        $this->traceable = $traceable;
    }

    public function __invoke(UriInterface $uri)
    {
        if ($this->traceable && !$uri instanceof TraceableUri) {
            $uri = new TraceableUri($uri);
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

    protected function createUri($uri)
    {
        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        if (!$uri instanceof TraceableUri) {
            $uri = new TraceableUri($uri);
        }

        return $uri;
    }
}
