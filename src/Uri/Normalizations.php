<?php

namespace LastCall\Crawler\Uri;

use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Uri;

class Normalizations
{
    /**
     * Characters that do not need to be encoded in URLs, and could be unescaped.
     *
     * @var array
     */
    private static $unreservedChars = array(
        '%30' => '0',
        '%31' => '1',
        '%32' => '2',
        '%33' => '3',
        '%34' => '4',
        '%35' => '5',
        '%36' => '6',
        '%37' => '7',
        '%38' => '8',
        '%39' => '9',
        '%61' => 'a',
        '%62' => 'b',
        '%63' => 'c',
        '%64' => 'd',
        '%65' => 'e',
        '%66' => 'f',
        '%67' => 'g',
        '%68' => 'h',
        '%69' => 'i',
        '%6a' => 'j',
        '%6b' => 'k',
        '%6c' => 'l',
        '%6d' => 'm',
        '%6e' => 'n',
        '%6f' => 'o',
        '%70' => 'p',
        '%71' => 'q',
        '%72' => 'r',
        '%73' => 's',
        '%74' => 't',
        '%75' => 'u',
        '%76' => 'v',
        '%77' => 'w',
        '%78' => 'x',
        '%79' => 'y',
        '%7a' => 'z',
        '%41' => 'A',
        '%42' => 'B',
        '%43' => 'C',
        '%44' => 'D',
        '%45' => 'E',
        '%46' => 'F',
        '%47' => 'G',
        '%48' => 'H',
        '%49' => 'I',
        '%4a' => 'J',
        '%4b' => 'K',
        '%4c' => 'L',
        '%4d' => 'M',
        '%4e' => 'N',
        '%4f' => 'O',
        '%50' => 'P',
        '%51' => 'Q',
        '%52' => 'R',
        '%53' => 'S',
        '%54' => 'T',
        '%55' => 'U',
        '%56' => 'V',
        '%57' => 'W',
        '%58' => 'X',
        '%59' => 'Y',
        '%5a' => 'Z',
        '%2D' => '-',
        '%5F' => '_',
    );

    public static function resolve(UriInterface $base)
    {
        return function (UriInterface $relative) use ($base) {
            return Uri::resolve($base, $relative);
        };
    }

    /**
     * Lowercase the scheme and host segments of the URL.
     *
     * This is considered a "safe" normalization as per RFC 3986
     *
     * @return \Closure
     */
    public static function lowercaseHostname()
    {
        return function (UriInterface $uri) {
            $host = $uri->getHost();

            $lower = mb_strtolower($host);
            if ($lower !== $host) {
                $uri = $uri->withHost($lower);
            }

            return $uri;
        };
    }

    /**
     * Convert all hex encoded URL characters to use uppercase hex.
     *
     * This is considered a "safe" normalization as per RFC 3986
     *
     * @return \Closure
     */
    public static function capitalizeEscaped()
    {
        return function (UriInterface $uri) {
            foreach (['Host', 'Path', 'Query', 'Fragment'] as $partName) {
                $part = $uri->{"get$partName"}();
                if (!empty($part) && strpos($part, '%') !== false) {
                    $upper = preg_replace_callback('/%[0-9a-f]{2}+/', function ($matches) {
                        return strtoupper($matches[0]);
                    }, $part);
                    if ($upper !== $part) {
                        $uri = $uri->{"with$partName"}($upper);
                    }
                }
            }

            return $uri;
        };
    }

    /**
     * Decode hex-encoded characters that do not need to be encoded.
     *
     * This is considered a "safe" normalization as per RFC 3986
     *
     * @return \Closure
     */
    public static function decodeUnreserved()
    {
        $regex = '/('.implode('|', array_keys(self::$unreservedChars)).')+/';

        return function (UriInterface $uri) use ($regex) {
            foreach (['Host', 'Path', 'Query', 'Fragment'] as $partName) {
                $part = $uri->{"get$partName"}();
                if (!empty($part) && strpos($part, '%') !== false && preg_match($regex, $part)) {
                    $fixed = preg_replace_callback($regex, function ($matches) {
                        return rawurldecode($matches[0]);
                    }, $part);
                    $uri = $uri->{"with$partName"}($fixed);
                }
            }

            return $uri;
        };
    }

    /**
     * Add a trailing slash to any paths that don't have an extension.
     *
     * This is usually a safe normalization.
     *
     * @return \Closure
     */
    public static function addTrailingSlash()
    {
        return function (UriInterface $uri) {
            $path = $uri->getPath();
            if (substr($path, -1) !== '/') {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if (!$ext) {
                    $uri = $uri->withPath($path.'/');
                }
            }

            return $uri;
        };
    }

    /**
     * Strip off an index page (index.html, index.php, etc).
     *
     * This is a breaking normalization.  It may or may not be safe to use,
     * depending on the server setup.
     *
     * @param string $indexRegex
     *
     * @return \Closure
     */
    public static function dropIndex()
    {
        return function (UriInterface $uri) {
            $path = $uri->getPath();
            if (preg_match('@(?<=^|/)(index|default)\.[a-z]{2,4}$@', $path)) {
                $uri = $uri->withPath(preg_replace('@(?<=^|/)(index|default)\.[a-z]{2,4}$@', '', $path));
            }

            return $uri;
        };
    }

    /**
     * Strip off the URL fragment (#fragment).
     *
     * This is usually a safe normalization.
     *
     * @return \Closure
     */
    public static function dropFragment()
    {
        return function (UriInterface $uri) {
            if ($uri->getFragment()) {
                $uri = $uri->withFragment('');
            }

            return $uri;
        };
    }

    /**
     * Change the scheme according to a map.
     *
     * @param array $map
     *
     * @return \Closure
     */
    public static function rewriteScheme(array $map)
    {
        return function (UriInterface $uri) use ($map) {
            $originalScheme = $scheme = $uri->getScheme();
            while (isset($map[$scheme])) {
                $scheme = $map[$scheme];
            }

            if ($originalScheme !== $scheme) {
                $uri = $uri->withScheme($scheme);
            }

            return $uri;
        };
    }

    /**
     * Change the hostname according to a map.
     *
     * @param array $map
     *
     * @return \Closure
     */
    public static function rewriteHost(array $map)
    {
        return function (UriInterface $uri) use ($map) {
            $originalHost = $host = $uri->getHost();
            while (isset($map[$host])) {
                $host = $map[$host];
            }
            if ($host !== $originalHost) {
                $uri = $uri->withHost($host);
            }

            return $uri;
        };
    }

    /**
     * Sort query parameters alphabetically.
     *
     * @return \Closure
     */
    public static function sortQuery()
    {
        return function (UriInterface $uri) {
            $query = $uri->getQuery();
            if (!empty($query) && strpos($query, '&') !== false) {
                $params = \GuzzleHttp\Psr7\parse_query($query);
                ksort($params);
                $newQuery = \GuzzleHttp\Psr7\build_query($params);
                if ($newQuery !== $query) {
                    $uri = $uri->withQuery($newQuery);
                }
            }

            return $uri;
        };
    }
}
