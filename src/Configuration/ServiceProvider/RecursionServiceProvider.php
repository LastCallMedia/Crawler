<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Handler\Discovery\AssetDiscoverer;
use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Handler\Uri\UriRecursor;
use LastCall\Crawler\Uri\Matcher;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Psr\Http\Message\UriInterface;

/**
 * Provides "recursion" services for the crawler.
 *
 * This service provider adds URL discovery subscribers and URL recursion
 * subscribers to respond to crawler events and add discovered URLs back into
 * the queue.
 */
class RecursionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['normalizer.link'] = function () use ($pimple) {
            return $pimple['normalizer'];
        };
        $pimple['normalizer.asset'] = function () use ($pimple) {
            return $pimple['normalizer'];
        };
        $pimple['normalizer.redirect'] = function () use ($pimple) {
            return $pimple['normalizer'];
        };

        $pimple['discoverer.link'] = function () use ($pimple) {
            return new LinkDiscoverer($pimple['normalizer.link']);
        };
        $pimple['discoverer.asset'] = function () use ($pimple) {
            return new AssetDiscoverer($pimple['normalizer.asset']);
        };
        $pimple['discoverer.redirect'] = function () use ($pimple) {
            return new RedirectDiscoverer($pimple['normalizer.redirect']);
        };

        $pimple['request_factory.internal_html'] = $pimple->protect(function (UriInterface $uri) use ($pimple) {
            return new Request('GET', $uri);
        });
        $pimple['request_factory.internal_asset'] = $pimple->protect(function (UriInterface $uri) use ($pimple) {
            return new Request('HEAD', $uri);
        });

        $pimple['matcher.internal_html'] = function () use ($pimple) {
            return Matcher::all()
                ->add($pimple['matcher.internal'])
                ->add($pimple['matcher.html']);
        };
        $pimple['matcher.internal_asset'] = function () use ($pimple) {
            return Matcher::all()
                ->add($pimple['matcher.internal'])
                ->add($pimple['matcher.asset']);
        };

        $pimple['recursor.internal_html'] = function () use ($pimple) {
            return new UriRecursor($pimple['matcher.internal_html'], $pimple['request_factory.internal_html']);
        };
        $pimple['recursor.internal_asset'] = function () use ($pimple) {
            return new UriRecursor($pimple['matcher.internal_asset'], $pimple['request_factory.internal_asset']);
        };
    }
}
