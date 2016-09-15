<?php

namespace LastCall\Crawler\Configuration\Loader;

use LastCall\Crawler\Configuration\ConfigurationInterface;

/**
 * Defines a configuration file loader.
 */
interface ConfigurationLoaderInterface
{
    /**
     * Load a configuration from a file.
     *
     * @param string $filename
     *
     * @return ConfigurationInterface
     */
    public function loadFile($filename);
}
