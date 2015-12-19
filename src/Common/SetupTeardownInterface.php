<?php

namespace LastCall\Crawler\Common;

/**
 * Common interface for designating an object that requires
 * setup and teardown.
 */
interface SetupTeardownInterface
{
    public function onSetup();

    public function onTeardown();

}