<?php

namespace LastCall\Crawler\Test\Resources;

use LastCall\Crawler\Command\CrawlCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Session\SessionInterface;

class DummyCrawlCommand extends CrawlCommand
{
    private $crawler;

    public function setCrawler(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    protected function getCrawler(ConfigurationInterface $configuration, SessionInterface $session)
    {
        return $this->crawler;
    }
}
