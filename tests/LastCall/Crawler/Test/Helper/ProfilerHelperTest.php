<?php

namespace LastCall\Crawler\Test\Helper;


use LastCall\Crawler\Helper\ProfilerHelper;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\Console\Style\OutputStyle;

class ProfilerHelperTest extends \PHPUnit_Framework_TestCase {

    public function testGetTraceableDispatcher() {
        $helper = new ProfilerHelper();
        $dispatcher = $helper->getTraceableDispatcher(new EventDispatcher());
        $this->assertInstanceOf(TraceableEventDispatcher::class, $dispatcher);
    }

    public function testRenderProfile() {
        $helper = new ProfilerHelper();
        $dispatcher = $helper->getTraceableDispatcher(new EventDispatcher());
        $dispatcher->dispatch('foo');

        $io = $this->prophesize(OutputStyle::class);
        $io->table(['Listener', 'Time'], Argument::that(function($rows) {
            return count($rows) === 1 && $rows[0][0] === 'foo' && is_numeric($rows[0][1]);
        }))->shouldBeCalled();
        $helper->renderProfile($io->reveal());
    }
}