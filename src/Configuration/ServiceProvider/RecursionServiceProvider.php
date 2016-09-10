<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Handler\Discovery\RedirectDiscoverer;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Psr\Http\Message\UriInterface;

class RecursionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['recursion.request_factory'] = $pimple->protect(function (UriInterface $uri) use ($pimple) {
            return new Request('GET', $uri);
        });
        $pimple->extend('processors', function (array $processors) use ($pimple) {
            $processors['link'] = new LinkProcessor($pimple['matcher.internal_html'], $pimple['normalizer'], $pimple['recursion.request_factory']);

            return $processors;
        });

        $pimple->extend('subscribers', function (array $subscribers) use ($pimple) {
            $subscribers['redirect'] = new RedirectDiscoverer($pimple['matcher.internal_html'], $pimple['normalizer'], $pimple['recursion.request_factory']);

            return $subscribers;
        });
    }
}
