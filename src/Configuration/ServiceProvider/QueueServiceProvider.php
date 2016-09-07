<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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

        // Register the queue setup and teardown listeners.
        $pimple->extend('listeners', function (array $listeners) use ($pimple) {
            $listeners[CrawlerEvents::SETUP]['queue.setup'] = [function () use ($pimple) {
                if ($pimple['queue'] instanceof SetupTeardownInterface) {
                    $pimple['queue']->onSetup();
                }
            }, 0];
            $listeners[CrawlerEvents::TEARDOWN]['queue.teardown'] = [function () use ($pimple) {
                if ($pimple['queue'] instanceof SetupTeardownInterface) {
                    $pimple['queue']->onTeardown();
                }
            }, 0];

            return $listeners;
        });
    }
}
