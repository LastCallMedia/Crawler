<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides logging services for the crawler.
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // Logs requests/responses as they happen.
        $pimple['logger.request'] = function () use ($pimple) {
            return new RequestLogger($pimple['logger']);
        };

        // Logs exceptions that are thrown during processing.
        $pimple['logger.exception'] = function () use ($pimple) {
            return new ExceptionLogger($pimple['logger']);
        };
    }
}
