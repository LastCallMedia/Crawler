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
        $pimple['html_extensions'] = ['', 'html', 'htm', 'php', 'asp', 'aspx', 'cfm'];
        $pimple['matcher'] = function () use ($pimple) {
            $uri = new Uri($pimple['baseUrl']);

            return Matcher::all()
                ->schemeIs(['http', 'https'])
                ->hostIs($uri->getHost());
        };
        $pimple['html_matcher'] = function () use ($pimple) {
            $matcher = clone $pimple['matcher'];
            $matcher->pathExtensionIs($pimple['html_extensions']);

            return $matcher;
        };
    }
}
