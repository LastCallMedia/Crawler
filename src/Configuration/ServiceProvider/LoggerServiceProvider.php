<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\NullLogger;

/**
 * Provides logging services for the crawler.
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // Any PSR-3 compatible logger.
        $pimple['logger'] = function () {
            return new NullLogger();
        };

        // Add logging subscribers
        $pimple->extend('subscribers', function (array $subscribers) use ($pimple) {
            $subscribers['requestLogger'] = new RequestLogger($pimple['logger']);
            $subscribers['exceptionLogger'] = new ExceptionLogger($pimple['logger']);

            return $subscribers;
        });
    }
}
