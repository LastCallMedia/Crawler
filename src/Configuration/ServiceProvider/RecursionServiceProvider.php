<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Handler\Discovery\AssetDiscoverer;
use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use LastCall\Crawler\Handler\Uri\UriRecursor;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Psr\Http\Message\UriInterface;

class RecursionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['request_factory.internal_html'] = $pimple->protect(function (UriInterface $uri) use ($pimple) {
            return new Request('GET', $uri);
        });
        $pimple['request_factory.internal_asset'] = $pimple->protect(function (UriInterface $uri) {
            return new Request('HEAD', $uri);
        });

        $pimple->extend('subscribers', function (array $subscribers) use ($pimple) {
            $subscribers['discovery.link'] = new LinkDiscoverer($pimple['normalizer']);
            $subscribers['discovery.asset'] = new AssetDiscoverer($pimple['normalizer']);
            $subscribers['discovery.redirect'] = new RedirectDiscoverer($pimple['normalizer']);

            $subscribers['uri_recursor.internal_html'] = new UriRecursor($pimple['matcher.internal_html'], $pimple['request_factory.internal_html']);
            $subscribers['uri_recursor.internal_asset'] = new UriRecursor($pimple['matcher.internal_asset'], $pimple['request_factory.internal_asset']);

            return $subscribers;
        });
    }
}
