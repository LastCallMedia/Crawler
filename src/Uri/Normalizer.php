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
        foreach ($this->handlers as $handler) {
            $uri = $handler($uri);
        }

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

    public static function resolve(UriInterface $base)
    {
        return function (UriInterface $relative) use ($base) {
            return Uri::resolve($base, $relative);
        };
    }

    /**
     * Strip a trailing slash off of the url path.
     *
     * @return \Closure
     */
    public static function stripTrailingSlash()
    {
        return function (UriInterface $uri) {
            return substr($uri->getPath(),
                -1) === '/' ? $uri->withPath(substr($uri->getPath(), 0,
                strlen($uri->getPath()) - 1)) : $uri;
        };
    }

    /**
     * Convert the casing of the URL to all upper or lower case.
     *
     * @param string $case
     *
     * @return \Closure
     */
    public static function normalizeCase($case = 'lower')
    {
        if (!in_array($case, ['upper', 'lower'])) {
            throw new \InvalidArgumentException(sprintf('Invalid case \'%s\'',
                (string) $case));
        }

        return function (UriInterface $uri) use ($case) {

            switch ($case) {
                case 'lower':
                    $ret = $uri;
                    $ret = (empty($ret->getHost()) || ctype_lower($ret->getHost())) ? $ret : $ret->withHost(mb_strtolower($ret->getHost()));
                    $ret = (empty($ret->getPath()) || ctype_lower($ret->getPath())) ? $ret : $ret->withPath(mb_strtolower($ret->getPath()));
                    $ret = (empty($ret->getFragment()) || ctype_lower($ret->getFragment())) ? $ret : $ret->withFragment(mb_strtolower($ret->getFragment()));

                    return $ret;
                case 'upper':
                    $ret = $uri;
                    $ret = (empty($ret->getHost()) || ctype_upper($ret->getHost())) ? $ret : $ret->withHost(mb_strtoupper($ret->getHost()));
                    $ret = (empty($ret->getPath()) || ctype_upper($ret->getPath())) ? $ret : $ret->withPath(mb_strtoupper($ret->getPath()));
                    $ret = (empty($ret->getFragment()) || ctype_upper($ret->getFragment())) ? $ret : $ret->withFragment(mb_strtouppers($ret->getFragment()));

                    return $ret;
            }
        };
    }

    /**
     * Lowercase the scheme and host segments of the URL.
     *
     * This is considered a "safe" normalization as per RFC 3986
     *
     * @return \Closure
     */
    public static function lowercaseSchemeAndHost()
    {
        return function (UriInterface $uri) {
            $scheme = $uri->getScheme();
            if (!ctype_lower($scheme)) {
                $uri = $uri->withScheme(strtolower($scheme));
            }
            $host = $uri->getHost();

            if (!ctype_lower($host)) {
                // Only convert ASCII A-Z to lowercase.
                $lower = preg_replace_callback('/[A-Z]+/', function ($matches) {
                    return strtolower($matches[0]);
                }, $host);
                if ($lower !== $host) {
                    $uri = $uri->withHost($lower);
                }
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
                if (preg_match('/%[0-9a-f]{2}/', $part)) {
                    $upper = preg_replace_callback('/%[0-9a-f]{2}+/', function ($matches) {
                        return strtoupper($matches[0]);
                    }, $part);
                    $uri = $uri->{"with$partName"}($upper);
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
                if (preg_match($regex, $part)) {
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
                    $uri = $uri->withPath($uri->getPath().'/');
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
    public static function dropIndex(
        $indexRegex = '@(?<=^|/)(index|default)\.[a-z]{2,4}$@'
    ) {
        return function (UriInterface $uri) use ($indexRegex) {
            $path = $uri->getPath();
            if (preg_match($indexRegex, $path)) {
                $uri = $uri->withPath(preg_replace($indexRegex, '', $path));
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
            return $uri->getFragment() ? $uri->withFragment(false) : $uri;
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
            $scheme = $uri->getScheme();
            if (isset($map[$scheme])) {
                $uri = $uri->withScheme($map[$scheme]);
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
            $host = $uri->getHost();
            if (isset($map[$host])) {
                $uri = $uri->withHost($map[$host]);
            }

            return $uri;
        };
    }
}
