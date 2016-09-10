<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class LinkServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple->extend('processors', function (array $processors) use ($pimple) {
            $processors['link'] = new LinkProcessor($pimple['matcher.internal_html'], $pimple['normalizer']);

            return $processors;
        });
    }
}
