<?php

namespace LastCall\Crawler\Test\Resources;

use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Helper\PreloadedCrawlerHelper;
use LastCall\Crawler\Reporter\ReporterInterface;
use LastCall\Crawler\Session\SessionInterface;

class DummyCrawlerHelper extends PreloadedCrawlerHelper
{
    private $session;
    private $crawler;

    public function __construct(
        ConfigurationInterface $config = null,
        SessionInterface $session = null,
        Crawler $crawler = null
    ) {
        $this->session = $session;
        $this->crawler = $crawler;
        parent::__construct($config);
    }

    public function getSession(
        ConfigurationInterface $config,
        ReporterInterface $reporter = null
    ) {
        return $this->session ?: parent::getSession($config, $reporter);
    }

    public function getCrawler(
        ConfigurationInterface $config,
        SessionInterface $session
    ) {
        return $this->crawler ?: parent::getCrawler($config, $session);
    }
}
