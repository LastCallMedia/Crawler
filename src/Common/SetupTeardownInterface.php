<?php

namespace LastCall\Crawler\Common;

/**
 * Defines an object that executes setup and teardown tasks.
 */
interface SetupTeardownInterface
{
    /**
     * Execute setup tasks.
     */
    public function onSetup();

    /**
     * Execute teardown tasks.
     */
    public function onTeardown();
}
