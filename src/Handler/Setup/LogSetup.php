<?php

namespace LastCall\Crawler\Handler\Setup;

use LastCall\Crawler\CrawlerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Prepares and removes log directories.
 *
 * @todo: Is this even needed?
 */
class LogSetup implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SETUP => 'onSetup',
            CrawlerEvents::TEARDOWN => 'onTeardown',
        ];
    }

    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function onSetup()
    {
        (new Filesystem())->mkdir($this->dir);
    }

    public function onTeardown()
    {
        (new Filesystem())->remove(glob($this->dir.'/*.log'));
    }
}
