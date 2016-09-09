<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Matches URLs against a predefined set of conditions.
 */
class Matcher implements MatcherInterface
{
    const ALL = 'all';
    const ANY = 'any';

    private $mode;
    private $handlers = [];

    /**
     * Create a new matcher instance that requires all conditions to be matched.
     *
     * @return \LastCall\Crawler\Uri\Matcher
     */
    public static function all()
    {
        return new self(self::ALL);
    }

    /**
     * Create a new matcher that requires any condition to be matched.
     *
     * @return \LastCall\Crawler\Uri\Matcher
     */
    public static function any()
    {
        return new self(self::ANY);
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

    public function __invoke(UriInterface $uri)
    {
        return $this->matches($uri);
    }

    public function matches(UriInterface $uri)
    {
        if (empty($this->handlers)) {
            return true;
        }
        if (self::ALL === $this->mode) {
            return $this->matchesAll($uri);
        } else {
            return $this->matchesAny($uri);
        }
    }

    private function matchesAll(UriInterface $uri)
    {
        foreach ($this->handlers as $handler) {
            if (true !== $handler($uri)) {
                return false;
            }
        }

        return true;
    }

    private function matchesAny(UriInterface $uri)
    {
        foreach ($this->handlers as $handler) {
            if (false !== $handler($uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a new condition set that requires all conditions to be fulfilled.
     *
     * @return self
     */
    public function andAll()
    {
        return $this->handlers[] = new self(self::ALL);
    }

    /**
     * Return a new condition set that requires any condition to be fulfilled.
     *
     * @return self
     */
    public function andAny()
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
        return $this->addNot(MatcherAssert::always());
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
     * Add a condition that the scheme does not match exactly.
     *
     * @param $schemes
     *
     * @return \LastCall\Crawler\Uri\Matcher
     */
    public function schemeIsNot($schemes)
    {
        return $this->addNot(MatcherAssert::schemeIs($schemes));
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
     * Add a condition that the scheme does not match a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function schemeNotMatches($patterns)
    {
        return $this->addNot(MatcherAssert::schemeMatches($patterns));
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
     * Add a condition that the host does not match exactly.
     *
     * @param string|string[] $hosts
     *
     * @return self
     */
    public function hostIsNot($hosts)
    {
        return $this->addNot(MatcherAssert::hostIs($hosts));
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
     * Add a condition that the host does not match a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function hostNotMatches($patterns)
    {
        return $this->addNot(MatcherAssert::hostMatches($patterns));
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
     * Add a condition that the port does not match exactly.
     *
     * @param int|int[]|null $ports
     *
     * @return self
     */
    public function portIsNot($ports)
    {
        return $this->addNot(MatcherAssert::portIs($ports));
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
     * Add a condition that the port is outside a range.
     *
     * @param int $min
     * @param int $max
     *
     * @return self
     */
    public function portNotIn($min, $max)
    {
        return $this->addNot(MatcherAssert::portIn($min, $max));
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
     * Add a condition that the path does not match exactly.
     *
     * @param string|string[] $paths
     *
     * @return self
     */
    public function pathIsNot($paths)
    {
        return $this->addNot(MatcherAssert::pathIs($paths));
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
     * Add a condition that the path does not end in a certain extension.
     *
     * @param string|string[] $exts
     *
     * @return self
     */
    public function pathExtensionIsNot($exts)
    {
        return $this->addNot(MatcherAssert::pathExtensionIs($exts));
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
     * Add a condition that the path does not match a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function pathNotMatches($patterns)
    {
        return $this->addNot(MatcherAssert::pathMatches($patterns));
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
     * Add a condition that the query string does not match exactly.
     *
     * @param string|string[] $queries
     *
     * @return self
     */
    public function queryIsNot($queries)
    {
        return $this->addNot(MatcherAssert::queryIs($queries));
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
     * Add a condition that the query string does not match a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function queryNotMatches($patterns)
    {
        return $this->addNot(MatcherAssert::queryMatches($patterns));
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
     * Add a condition that the fragment does not match exactly.
     *
     * @param string|string[] $fragments
     *
     * @return self
     */
    public function fragmentIsNot($fragments)
    {
        return $this->addNot(MatcherAssert::fragmentIs($fragments));
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
     * Add a condition that the fragment does not match a PCRE pattern.
     *
     * @param string|string[] $patterns
     *
     * @return self
     */
    public function fragmentNotMatches($patterns)
    {
        return $this->addNot(MatcherAssert::fragmentMatches($patterns));
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

    /**
     * Add an arbitrary callback function to the matcher, which will be negated.
     *
     * The callback must accept a `UriInterface` object as a parameter,
     * and return a boolean value indicating whether the URI matches.
     *
     * @param callable $handler
     *
     * @return self
     */
    public function addNot(callable $handler)
    {
        $this->handlers[] = MatcherAssert::not($handler);

        return $this;
    }
}
