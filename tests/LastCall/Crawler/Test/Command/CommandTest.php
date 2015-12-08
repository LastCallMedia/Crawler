<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Helper\CrawlerHelper;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;


abstract class CommandTest extends \PHPUnit_Framework_TestCase
{

    protected function getMockHelperSet($configuration, $crawler) {
        $crawlerHelper = $this->prophesize(CrawlerHelper::class);
        $crawlerHelper->getName()->willReturn('crawler');
        $crawlerHelper->getConfiguration('test.php', Argument::type(OutputInterface::class))->willReturn($configuration);
        $crawlerHelper->getCrawler($configuration, Argument::not(TRUE))->willReturn($crawler);


        $set = $this->prophesize(HelperSet::class);
        $set->get('crawler')->willReturn($crawlerHelper);

        return $set->reveal();
    }
}