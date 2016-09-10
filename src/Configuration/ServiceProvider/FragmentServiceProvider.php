<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Fragment\Parser\CSSSelectorParser;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use LastCall\Crawler\Handler\Fragment\FragmentHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FragmentServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['parsers'] = function () {
            $parsers = [
                'xpath' => new XPathParser(),
            ];
            if (class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
                $parsers['css'] = new CSSSelectorParser();
            }

            return $parsers;
        };

        $pimple['processors'] = function () use ($pimple) {
            return [];
        };

        $pimple->extend('subscribers', function (array $subscribers) use ($pimple) {
            $subscribers['fragment'] = new FragmentHandler($pimple['parsers'], $pimple['processors']);

            return $subscribers;
        });
    }
}
