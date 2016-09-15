<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Matcher;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides URL matching services for the crawler.
 */
class MatcherServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // Configure specialized matchers.
        $pimple['html_extensions'] = ['', 'html', 'htm', 'php', 'asp', 'aspx', 'cfm'];
        $pimple['asset_extensions'] = ['css', 'js', 'png', 'jpeg', 'jpg', 'svg'];

        // Matches any path that has one of the html extensions.
        $pimple['matcher.html'] = function () use ($pimple) {
            return Matcher::all()
                ->pathExtensionIs($pimple['html_extensions']);
        };

        // Matches any path that has one of the asset extensions.
        $pimple['matcher.asset'] = function () use ($pimple) {
            return Matcher::all()
                ->pathExtensionIs($pimple['asset_extensions']);
        };

        // Matches any path that is considered "internal" to the crawl.
        $pimple['matcher.internal'] = function () use ($pimple) {
            $uri = new Uri($pimple['base_url']);

            return Matcher::all()
                ->schemeIs($uri->getScheme())
                ->hostIs($uri->getHost())
                ->pathMatches('~^'.preg_quote($uri->getPath(), '~').'~');
        };

        // Matches any path that is both internal and HTML.
        $pimple['matcher.internal_html'] = function () use ($pimple) {
            return Matcher::all()
                ->add($pimple['matcher.internal'])
                ->add($pimple['matcher.html']);
        };

        // Matches any path that is both internal and an asset.
        $pimple['matcher.internal_asset'] = function () use ($pimple) {
            return Matcher::all()
                ->add($pimple['matcher.internal'])
                ->add($pimple['matcher.asset']);
        };
    }
}
