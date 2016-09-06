<?php


namespace LastCall\Crawler\Configuration\Loader;

use LastCall\Crawler\Configuration\ConfigurationInterface;


class PHPConfigurationLoader implements ConfigurationLoaderInterface
{
    public function loadFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception(sprintf('Configuration file %s does not exist.', $filename));
        }
        $configuration = require $filename;

        if (!$configuration || !$configuration instanceof ConfigurationInterface) {
            throw new \Exception(sprintf('Configuration must implement %s', ConfigurationInterface::class));
        }

        return $configuration;
    }


}