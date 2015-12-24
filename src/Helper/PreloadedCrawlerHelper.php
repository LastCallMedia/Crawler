<?php

namespace LastCall\Crawler\Helper;

use LastCall\Crawler\Configuration\ConfigurationInterface;

class PreloadedCrawlerHelper extends AbstractCrawlerHelper
{
    private $configuration;

    public function __construct(ConfigurationInterface $config)
    {
        $this->configuration = $config;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}
