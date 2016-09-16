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

        // Logs requests/responses as they happen.
        $pimple['subscriber.request_logger'] = function() use ($pimple) {
            return new RequestLogger($pimple['logger']);
        };

        $pimple['subscriber.exception_logger'] = function() use ($pimple) {
            return new ExceptionLogger($pimple['logger']);
        };
    }
}
