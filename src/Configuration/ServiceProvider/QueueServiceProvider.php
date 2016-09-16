<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides a request queue for the crawler.
 *
 * If a Doctrine connection has been defined, a database backed queue
 * will be used.  Otherwise, an in-memory array queue will be used instead.
 */
class QueueServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['queue'] = function () use ($pimple) {
            // Prefer doctrine queue if it is available.
            if (isset($pimple['doctrine'])) {
                return new DoctrineRequestQueue($pimple['doctrine']);
            }

            // Fall back to the simple array queue if it is not.
            return new ArrayRequestQueue();
        };
    }
}
