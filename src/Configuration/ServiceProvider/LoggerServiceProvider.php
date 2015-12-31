<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\NullLogger;

class LoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['logger'] = function () {
            return new NullLogger();
        };
        $pimple->extend('subscribers', function (array $subscribers) use ($pimple) {
            $subscribers['requestLogger'] = new RequestLogger($pimple['logger']);
            $subscribers['exceptionLogger'] = new ExceptionLogger($pimple['logger']);

            return $subscribers;
        });
    }
}
