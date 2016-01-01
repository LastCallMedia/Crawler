<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Matcher;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MatcherServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['matcher'] = function () use ($pimple) {
            $uri = new Uri($pimple['base_url']);

            // Match any URI with an http or https scheme
            // and the same hostname as our base URL.
            return Matcher::all()
                ->schemeIs(['http', 'https'])
                ->hostIs($uri->getHost());
        };

        // Configure an alternate matcher that only matches links
        // that should contain HTML content.
        // This matcher requires that the base matcher conditions are
        // met, and that the file extension is one that we know contains
        // HTML content.
        $pimple['html_extensions'] = ['', 'html', 'htm', 'php', 'asp', 'aspx', 'cfm'];
        $pimple['html_matcher'] = function () use ($pimple) {
            $matcher = clone $pimple['matcher'];
            $matcher->pathExtensionIs($pimple['html_extensions']);

            return $matcher;
        };
    }
}
