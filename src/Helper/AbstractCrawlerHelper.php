<?php


namespace LastCall\Crawler\Helper;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Handler\Reporting\CrawlerStatusReporter;
use LastCall\Crawler\Reporter\ReporterInterface;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Session\SessionInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractCrawlerHelper extends Helper implements CrawlerHelperInterface
{

    public function getName()
    {
        return 'crawler';
    }

    public function getSession(
        ConfigurationInterface $config,
        ReporterInterface $reporter = null
    ) {
        $dispatcher = new EventDispatcher();
        if ($reporter) {
            $subscriber = new CrawlerStatusReporter($config->getQueue(),
                [$reporter]);
            $dispatcher->addSubscriber($subscriber);
        }

        return Session::createFromConfig($config, $dispatcher);
    }

    public function getCrawler(
        ConfigurationInterface $config,
        SessionInterface $session
    ) {
        return new Crawler($session, $config->getClient());
    }

}