<?php

namespace LastCall\Crawler\Handler\Setup;

use LastCall\Crawler\Common\SetupTeardownInterface;
use LastCall\Crawler\CrawlerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Wraps a SetupTeardownInterface instance to invoke setup and teardown events
 * on the instance.
 */
class SetupTeardownWrapper implements EventSubscriberInterface
{
    /**
     * @var \LastCall\Crawler\Common\SetupTeardownInterface
     */
    private $wrapped;

    public static function getSubscribedEvents()
    {
        return [
            CrawlerEvents::SETUP => 'onSetup',
            CrawlerEvents::TEARDOWN => 'onTeardown',
        ];
    }

    public function __construct(SetupTeardownInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function onSetup()
    {
        $this->wrapped->onSetup();
    }

    public function onTeardown()
    {
        $this->wrapped->onTeardown();
    }
}
