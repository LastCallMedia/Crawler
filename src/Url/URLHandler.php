<?php

namespace LastCall\Crawler\Url;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * Handles URL normalization and matching for the crawler.
 *
 * There are two primary components to the URLHandler, the matcher, and the
 * normalizer.
 *
 * The matcher is in charge of pattern matching, and can be fed regex patterns
 * indicating whether to include or exclude certain URLs, and what type of
 * content a certain URL might contain (HTML, or file content).
 *
 * The normalizer is in charge of making URLs found throughout the site
 * consistent. It can utilize different configurations to manipulate the URLs.
 */
class URLHandler
{

    protected $baseUrl;

    protected $currentUrl;

    protected $matcher;

    protected $normalizer;

    public static function uriFor($url)
    {
        return $url instanceof UriInterface ? $url : new Uri($url);
    }

    public function __construct(
      $baseUrl,
      $currentUrl = null,
      Matcher $matcher = null,
      Normalizer $normalizer = null
    ) {
        $this->baseUrl = static::uriFor($baseUrl);
        $this->currentUrl = static::uriFor($currentUrl ? $currentUrl : $baseUrl);
        $this->matcher = $matcher ? $matcher : new Matcher();
        $this->normalizer = $normalizer ? $normalizer : new Normalizer();
    }

    /**
     * Convert relative URLs into absolute URLs.
     *
     * Does not affect URLs that are already absolute.
     *
     * @param string|\Psr\Http\Message\UriInterface $url
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public function absolutizeUrl($url)
    {
        if (is_string($url)) {
            if (strpos($url, 'http') === 0) {
                return $url;
            } elseif (strpos($url, '#') === 0) {
                return $this->currentUrl->withFragment($url);
            } elseif (strpos($url, 'mailto:') === 0 || strpos($url,
                'javascript:') === 0
            ) {
                return false;
            }
        } elseif ($url instanceof UriInterface && $url->getScheme()) {
            return $url;
        }

        return Uri::resolve($this->currentUrl, $url);
    }

    /**
     * Normalize a URL, running all normalization steps against it.
     *
     * @param string|\Psr\Http\Message\UriInterface $uri
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public function normalizeUrl($uri)
    {
        return $this->normalizer->normalize($this->absolutizeUrl($uri));
    }

    /**
     * Check whether the given URL should be included in the current crawl.
     *
     * @param $url
     *
     * @return bool
     */
    public function includesUrl($url)
    {
        $_url = (string) $url; // Cast to a string here to avoid doing it 2x.
        return $this->matcher->matchesInclude($_url) && !$this->matcher->matchesExclude($_url);
    }

    /**
     * Check whether a URL is a file resource or not.
     *
     * This function only inspects the URL, it does not make a request.
     *
     * @param $url
     *
     * @return bool
     */
    public function isFile($url)
    {
        return $this->matcher->matchesFile($url);
    }

    /**
     * Check whether the URL represents HTML content or not.
     *
     * @param $url
     *
     * @return bool
     */
    public function isCrawlable($url)
    {
        return $this->matcher->matchesHTML($url);
    }

    /**
     * Get the current URL we are on.
     *
     * @return \GuzzleHttp\Psr7\Uri
     */
    public function getCurrentUrl()
    {
        return $this->currentUrl;
    }

    /**
     * Get the base URL the crawler was initialized with.
     *
     * @return \GuzzleHttp\Psr7\Uri
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Create a new instance of the URLHandler for a specific URL.
     *
     * @param $url
     *
     * @return static
     */
    public function forUrl($url)
    {
        return new static($this->baseUrl, $url, $this->matcher,
          $this->normalizer);
    }
}