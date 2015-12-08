<?php

namespace LastCall\Crawler\Listener;


use LastCall\Crawler\Crawler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class LogSetupSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents() {
        return array(
            Crawler::SETUP => 'onSetup',
            Crawler::TEARDOWN => 'onTeardown',
        );
    }

    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function onSetup() {
        (new Filesystem())->mkdir($this->dir);
    }

    public function onTeardown() {
        (new Filesystem())->remove(glob($this->dir . '/*.log'));
    }

}