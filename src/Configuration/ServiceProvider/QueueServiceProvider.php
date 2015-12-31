<?php

namespace LastCall\Crawler\Configuration\ServiceProvider;

use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Queue\DoctrineRequestQueue;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class QueueServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['queue'] = function () use ($pimple) {
            if (isset($pimple['doctrine'])) {
                return new DoctrineRequestQueue($pimple['doctrine']);
            }

            return new ArrayRequestQueue();
        };
    }
}
