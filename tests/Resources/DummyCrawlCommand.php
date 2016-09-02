<?php

namespace LastCall\Crawler\Test\Resources;

use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;

class DummyCrawlCommand extends CrawlCommand
{
    private $crawler;

    public function setCrawler(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    protected function getCrawler(ConfigurationInterface $configuration)
    {
        return $this->crawler;
    }
}
