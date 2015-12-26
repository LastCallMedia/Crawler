<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

class Matcher
{
    const ALL = 'all';
    const ANY = 'any';

    private $mode;
    private $handlers = [];

    public static function create()
    {
        return new self(self::ALL);
    }

    private function __construct($mode)
    {
        $this->mode = $mode;
    }

    public function __invoke(UriInterface $uri)
    {
        if (self::ALL === $this->mode) {
            foreach ($this->handlers as $handler) {
                if (true !== $handler($uri)) {
                    return false;
                }
            }

            return true;
        } elseif (self::ANY === $this->mode) {
            foreach ($this->handlers as $handler) {
                if (false !== $handler($uri)) {
                    return true;
                }
            }

            return false;
        }
    }

    public function all()
    {
        return $this->handlers[] = new self(self::ALL);
    }

    public function any()
    {
        return $this->handlers[] = new self(self::ANY);
    }

    public function always()
    {
        return $this->add(MatcherAssert::always());
    }

    public function never()
    {
        return $this->add(MatcherAssert::never());
    }

    public function schemeIs($schemes)
    {
        return $this->add(MatcherAssert::schemeIs($schemes));
    }

    public function schemeMatches($patterns)
    {
        return $this->add(MatcherAssert::schemeMatches($patterns));
    }

    public function hostIs($hosts)
    {
        return $this->add(MatcherAssert::hostIs($hosts));
    }

    public function hostMatches($patterns)
    {
        return $this->add(MatcherAssert::hostMatches($patterns));
    }

    public function portIs($ports)
    {
        return $this->add(MatcherAssert::portIs($ports));
    }

    public function portIn($min, $max)
    {
        return $this->add(MatcherAssert::portIn($min, $max));
    }

    public function pathIs($paths)
    {
        return $this->add(MatcherAssert::pathIs($paths));
    }

    public function pathExtensionIs($exts)
    {
        return $this->add(MatcherAssert::pathExtensionIs($exts));
    }

    public function pathMatches($patterns)
    {
        return $this->add(MatcherAssert::pathMatches($patterns));
    }

    public function queryIs($queries)
    {
        return $this->add(MatcherAssert::queryIs($queries));
    }

    public function queryMatches($patterns)
    {
        return $this->add(MatcherAssert::queryMatches($patterns));
    }

    public function fragmentIs($fragments)
    {
        return $this->add(MatcherAssert::fragmentIs($fragments));
    }

    public function fragmentMatches($patterns)
    {
        return $this->add(MatcherAssert::fragmentMatches($patterns));
    }

    public function add(callable $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }
}
