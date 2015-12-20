<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Helper\CrawlerHelper;
use LastCall\Crawler\Helper\ProfilerHelper;
use LastCall\Crawler\Reporter\ReporterInterface;
use LastCall\Crawler\Session\SessionInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;


abstract class CommandTest extends \PHPUnit_Framework_TestCase
{

    protected function getMockHelperSet(
        $configuration,
        $crawler
    ) {
        $session = $this->prophesize(SessionInterface::class)->reveal();

        $crawlerHelper = $this->prophesize(CrawlerHelper::class);
        $crawlerHelper->getName()->willReturn('crawler');
        $crawlerHelper->getConfiguration('test.php',
            Argument::type(OutputInterface::class))->willReturn($configuration);

        $crawlerHelper->getSession($configuration,
            Argument::that(function ($arg) {
                return $arg === null || $arg instanceof ReporterInterface;
            }))->willReturn($session);

        $crawlerHelper->getCrawler($session, $configuration)
            ->willReturn($crawler);


        $set = $this->prophesize(HelperSet::class);
        $set->get('crawler')->willReturn($crawlerHelper);

        return $set->reveal();
    }
}