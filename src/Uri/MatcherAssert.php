<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;

/**
 * Assertions for the URI matcher.
 */
class MatcherAssert
{
    /**
     * Always match.
     *
     * @return \Closure
     */
    public static function always()
    {
        return function () {
            return true;
        };
    }

    /**
     * Require the inverse of any assertion.
     *
     * @param callable $assert
     *
     * @return \Closure
     */
    public static function not(callable $assert)
    {
        return function (UriInterface $uri) use ($assert) {
            return !$assert($uri);
        };
    }

    /**
     * Match an explicit scheme.
     *
     * @param string|string[] $schemes
     *
     * @return \Closure
     */
    public static function schemeIs($schemes)
    {
        $schemes = self::convertToKeyedArray($schemes);

        return function (UriInterface $uri) use ($schemes) {
            return isset($schemes[$uri->getScheme()]);
        };
    }

    /**
     * Match a scheme pattern.
     *
     * @param string|string[] $patterns
     *
     * @return \Closure
     */
    public static function schemeMatches($patterns)
    {
        $patterns = is_array($patterns) ? $patterns : [$patterns];

        return function (UriInterface $uri) use ($patterns) {
            $scheme = $uri->getScheme();
            foreach ($patterns as $pattern) {
                if (1 === preg_match($pattern, $scheme)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Match an explicit hostname.
     *
     * @param string|string[] $hosts
     *
     * @return \Closure
     */
    public static function hostIs($hosts)
    {
        $hosts = self::convertToKeyedArray($hosts);

        return function (UriInterface $uri) use ($hosts) {
            return isset($hosts[$uri->getHost()]);
        };
    }

    /**
     * Match a hostname pattern.
     *
     * @param string|string[] $patterns
     *
     * @return \Closure
     */
    public static function hostMatches($patterns)
    {
        $patterns = is_array($patterns) ? $patterns : [$patterns];

        return function (UriInterface $uri) use ($patterns) {
            $host = $uri->getHost();
            foreach ($patterns as $pattern) {
                if (1 === preg_match($pattern, $host)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Match an explicit port.
     *
     * @param int|null|array $ports
     *
     * @return \Closure
     */
    public static function portIs($ports)
    {
        $ports = is_array($ports) ? $ports : [$ports];

        return function (UriInterface $uri) use ($ports) {
            return in_array($uri->getPort(), $ports, true);
        };
    }

    /**
     * Match a port within a range.
     *
     * @param int $min
     * @param int $max
     *
     * @return \Closure
     */
    public static function portIn($min, $max)
    {
        return function (UriInterface $uri) use ($min, $max) {
            $port = $uri->getPort();

            return $min <= $port && $port <= $max;
        };
    }

    /**
     * Match an explicit path.
     *
     * @param string|string[] $paths
     *
     * @return \Closure
     */
    public static function pathIs($paths)
    {
        $paths = self::convertToKeyedArray($paths);

        return function (UriInterface $uri) use ($paths) {
            return isset($paths[$uri->getPath()]);
        };
    }

    /**
     * Match an explicit file extension.
     *
     * @param string|string[] $extensions
     *
     * @return \Closure
     */
    public static function pathExtensionIs($extensions)
    {
        $extensions = self::convertToKeyedArray($extensions);

        return function (UriInterface $uri) use ($extensions) {
            $ext = pathinfo($uri->getPath(), PATHINFO_EXTENSION);

            return isset($extensions[$ext]);
        };
    }

    /**
     * Match a path pattern.
     *
     * @param string|string[] $patterns
     *
     * @return \Closure
     */
    public static function pathMatches($patterns)
    {
        $patterns = is_array($patterns) ? $patterns : [$patterns];

        return function (UriInterface $uri) use ($patterns) {
            $path = $uri->getPath();
            foreach ($patterns as $pattern) {
                if (1 === preg_match($pattern, $path)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Match an explicit query string.
     *
     * @param string|string[] $queries
     *
     * @return \Closure
     */
    public static function queryIs($queries)
    {
        $queries = self::convertToKeyedArray($queries);

        return function (UriInterface $uri) use ($queries) {
            return isset($queries[$uri->getQuery()]);
        };
    }

    /**
     * Match a query string pattern.
     *
     * @param string|string[] $patterns
     *
     * @return \Closure
     */
    public static function queryMatches($patterns)
    {
        $patterns = is_array($patterns) ? $patterns : [$patterns];

        return function (UriInterface $uri) use ($patterns) {
            $query = $uri->getQuery();
            foreach ($patterns as $pattern) {
                if (1 === preg_match($pattern, $query)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Match an explicit fragment.
     *
     * @param string|string[] $fragments
     *
     * @return \Closure
     */
    public static function fragmentIs($fragments)
    {
        $fragments = self::convertToKeyedArray($fragments);

        return function (UriInterface $uri) use ($fragments) {
            return isset($fragments[$uri->getFragment()]);
        };
    }

    /**
     * Match a fragment pattern.
     *
     * @param string|string[] $patterns
     *
     * @return \Closure
     */
    public static function fragmentMatches($patterns)
    {
        $patterns = is_array($patterns) ? $patterns : [$patterns];

        return function (UriInterface $uri) use ($patterns) {
            $fragment = $uri->getFragment();
            foreach ($patterns as $pattern) {
                if (1 === preg_match($pattern, $fragment)) {
                    return true;
                }
            }

            return false;
        };
    }

    private static function convertToKeyedArray($args)
    {
        $args = is_array($args) ? $args : [$args];

        return array_flip($args);
    }
}
