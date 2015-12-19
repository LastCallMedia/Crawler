<?php

namespace LastCall\Crawler\Url;

/**
 * An alternate URL handler that caches results.
 */
class CachedUrlHandler extends URLHandler
{

    private $cache = [
        'normalizeUrl' => [],
        'absolutizeUrl' => [],
    ];

    public function normalizeUrl($url)
    {
        $key = $this->getCacheKey($url);
        if (!isset($this->cache['normalizeUrl'][$key])) {
            $this->cache['normalizeUrl'][$key] = parent::normalizeUrl($url);
        }

        return $this->cache['normalizeUrl'][$key];
    }

    private function getCacheKey($url)
    {
        if (is_object($url)) {
            return (string)$url;
        }

        return $url;
    }

    public function absolutizeUrl($url)
    {
        $key = $this->getCacheKey($url);
        if (!isset($this->cache['absolutizeUrl'][$key])) {
            $this->cache['absolutizeUrl'][$key] = parent::absolutizeUrl($url);
        }

        return $this->cache['absolutizeUrl'][$key];
    }
}