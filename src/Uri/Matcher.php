<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Matches URLs against a predefined set of conditions.
 */
class Matcher
{
    const ALL = 'all';
    const ANY = 'any';

    private $mode;
    private $handlers = [];

    /**
     * Create a new matcher instance.
     *
     * The root matcher requires that all conditions be fulfilled.
     * If you want to add optional conditions, you can use the `any`
     * method to return an OR branch.
     *
     * @return \LastCall\Crawler\Uri\Matcher
     */
    public static function create()
    {
        return new self(self::ALL);
    }

    /**
     * Matcher constructor.
     *
     * @param $mode
     */
    private function __construct($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Determine whether a given URL matches the conditions.
     *
     * @param \Psr\Http\Message\UriInterface $uri
     *
     * @return bool
     */
    public function __invoke(UriInterface $uri)
    {
        if (self::ALL === $this->mode) {
            foreach ($this->handlers as $handler) {
                if (true !== $handler($uri)) {
                    return false;
                }
            }

            return true;
        } else {
            foreach ($this->handlers as $handler) {
                if (false !== $handler($uri)) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * Return a new condition set that requires all conditions to be fulfilled.
     *
     * @return self
     */
    public function all()
    {
        return $this->handlers[] = new self(self::ALL);
    }

    /**
     * Return a new condition set that requires any condition to be fulfilled.
     *
     * @return self
     */
    public function any()
    {
        return $this->handlers[] = new self(self::ANY);
    }

    /**
     * Add a condition that will always be true.
     *
     * @return self
     */
    public function always()
    {
        return $this->add(MatcherAssert::always());
    }

    /**
     * Add a condition that will never be true.
     *
     * @return self
     */
    public function never()
    {
        return $this->add(MatcherAssert::never());
    }

    /**
     * Add a condition that the scheme matches exactly.
     *
     * @param string|string[] $schemes
     *
     * @return self
     */
    public function schemeIs($schemes)
    {
        return $this->add(MatcherAssert::schemeIs($schemes));
    }

    /**
     * Add a condition that the scheme matches a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function schemeMatches($patterns)
    {
        return $this->add(MatcherAssert::schemeMatches($patterns));
    }

    /**
     * Add a condition that the host matches exactly.
     *
     * @param string|string[] $hosts
     *
     * @return self
     */
    public function hostIs($hosts)
    {
        return $this->add(MatcherAssert::hostIs($hosts));
    }

    /**
     * Add a condition that the host matches a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function hostMatches($patterns)
    {
        return $this->add(MatcherAssert::hostMatches($patterns));
    }

    /**
     * Add a condition that the port matches exactly.
     *
     * @param int|int[]|null $ports
     *
     * @return self
     */
    public function portIs($ports)
    {
        return $this->add(MatcherAssert::portIs($ports));
    }

    /**
     * Add a condition that the port is inside a range.
     *
     * @param int $min
     * @param int $max
     *
     * @return self
     */
    public function portIn($min, $max)
    {
        return $this->add(MatcherAssert::portIn($min, $max));
    }

    /**
     * Add a condition that the path matches exactly.
     *
     * @param string|string[] $paths
     *
     * @return self
     */
    public function pathIs($paths)
    {
        return $this->add(MatcherAssert::pathIs($paths));
    }

    /**
     * Add a condition that the path ends in a certain extension.
     *
     * @param string|string[] $exts
     *
     * @return self
     */
    public function pathExtensionIs($exts)
    {
        return $this->add(MatcherAssert::pathExtensionIs($exts));
    }

    /**
     * Add a condition that the path matches a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function pathMatches($patterns)
    {
        return $this->add(MatcherAssert::pathMatches($patterns));
    }

    /**
     * Add a condition that the query string matches exactly.
     *
     * @param string|string[] $queries
     *
     * @return self
     */
    public function queryIs($queries)
    {
        return $this->add(MatcherAssert::queryIs($queries));
    }

    /**
     * Add a condition that the query string matches a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function queryMatches($patterns)
    {
        return $this->add(MatcherAssert::queryMatches($patterns));
    }

    /**
     * Add a condition that the fragment matches exactly.
     *
     * @param string|string[] $fragments
     *
     * @return self
     */
    public function fragmentIs($fragments)
    {
        return $this->add(MatcherAssert::fragmentIs($fragments));
    }

    /**
     * Add a condition that the fragment matches a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function fragmentMatches($patterns)
    {
        return $this->add(MatcherAssert::fragmentMatches($patterns));
    }

    /**
     * Add an arbitrary callback function to the matcher.
     *
     * The callback must accept a `UriInterface` object as a parameter,
     * and return a boolean value indicating whether the URI matches.
     *
     * @param callable $handler
     *
     * @return self
     */
    public function add(callable $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }
}
