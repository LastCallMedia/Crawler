<?php

namespace LastCall\Crawler\Common;

interface SetupTeardownInterface
{
    public function onSetup();

    public function onTeardown();

}