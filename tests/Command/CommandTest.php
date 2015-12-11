<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Helper\CrawlerHelper;
use LastCall\Crawler\Helper\ProfilerHelper;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;


abstract class CommandTest extends \PHPUnit_Framework_TestCase
{

    protected function getMockHelperSet(
        $configuration,
        $crawler,
        $profile = false
    ) {
        $crawlerHelper = $this->prophesize(CrawlerHelper::class);
        $crawlerHelper->getName()->willReturn('crawler');
        $crawlerHelper->getConfiguration('test.php',
            Argument::type(OutputInterface::class))->willReturn($configuration);
        $crawlerHelper->getCrawler($configuration, $profile)
            ->willReturn($crawler);

        $profilerHelper = $this->prophesize(ProfilerHelper::class);
        $profilerHelper->getName()->willReturn('profiler');
        if ($profile) {
            $profilerHelper->renderProfile(Argument::type(OutputStyle::class))
                ->shouldBeCalled();
        } else {
            $profilerHelper->renderProfile(Argument::type(OutputStyle::class))
                ->shouldNotBeCalled();
        }


        $set = $this->prophesize(HelperSet::class);
        $set->get('crawler')->willReturn($crawlerHelper);
        $set->get('profiler')->willReturn($profilerHelper);

        return $set->reveal();
    }
}