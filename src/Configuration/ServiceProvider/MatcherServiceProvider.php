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
    /**
     * The default set of HTML file extensions.
     *
     * @var array
     */
    protected static $html_extensions = ['', 'html', 'htm', 'php', 'asp', 'aspx', 'cfm'];

    /**
     * The default set of asset file extensions.
     *
     * @var array
     */
    protected static $asset_extensions = ['css', 'js', 'png', 'jpeg', 'jpg', 'svg'];

    public function register(Container $pimple)
    {
        // File extensions used to determine whether a URI points to an asset
        // or an HTML file.
        $pimple['html_extensions'] = static::$html_extensions;
        $pimple['asset_extensions'] = static::$asset_extensions;

        // Matches any URI we think points to an HTML page.
        $pimple['matcher.html'] = function () use ($pimple) {
            return Matcher::all()
                ->pathExtensionIs($pimple['html_extensions']);
        };

        // Matches any URI we think points to an asset file.
        $pimple['matcher.asset'] = function () use ($pimple) {
            return Matcher::all()
                ->pathExtensionIs($pimple['asset_extensions']);
        };

        // Matches any URI that is considered "in scope."
        $pimple['matcher.internal'] = function () use ($pimple) {
            $uri = new Uri($pimple['base_url']);

            return Matcher::all()
                ->schemeIs($uri->getScheme())
                ->hostIs($uri->getHost())
                ->pathMatches('~^'.preg_quote($uri->getPath(), '~').'~');
        };

        // Matches any URI that is both internal and HTML.
        $pimple['matcher.internal_html'] = function () use ($pimple) {
            return Matcher::all()
                ->add($pimple['matcher.internal'])
                ->add($pimple['matcher.html']);
        };

        // Matches any URI that is both internal and an asset.
        $pimple['matcher.internal_asset'] = function () use ($pimple) {
            return Matcher::all()
                ->add($pimple['matcher.internal'])
                ->add($pimple['matcher.asset']);
        };
    }
}
