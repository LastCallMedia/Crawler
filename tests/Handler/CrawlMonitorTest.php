<?php

namespace LastCall\Crawler\Test\Handler;

use LastCall\Crawler\Handler\CrawlMonitor;
use LastCall\Crawler\Queue\RequestQueueInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlMonitorTest extends \PHPUnit_Framework_TestCase
{
    public function testSetup()
    {
        $io = $this->prophesize(OutputStyle::class);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $io->success('Setup complete')->shouldBeCalled();

        $monitor = new CrawlMonitor($queue->reveal(), $io->reveal());
        $monitor->onSetup();
    }

    public function testTeardown()
    {
        $io = $this->prophesize(OutputStyle::class);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $io->success('Teardown complete')->shouldBeCalled();

        $monitor = new CrawlMonitor($queue->reveal(), $io->reveal());
        $monitor->onTeardown();
    }

    public function testStart()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $queue = $this->prophesize(RequestQueueInterface::class);

        $monitor = new CrawlMonitor($queue->reveal(), $io);
        $monitor->onStart();
        $this->assertOutputEquals(" Starting...\n", $output);
    }

    public function testFinish()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $queue = $this->prophesize(RequestQueueInterface::class);

        $monitor = new CrawlMonitor($queue->reveal(), $io);
        $monitor->onFinish();
        $this->assertOutputEquals(" Starting...\n Complete   \n\n", $output);
    }

    public function testOnSending()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->count()->willReturn(5);

        $monitor = new CrawlMonitor($queue->reveal(), $io);
        $monitor->onSending();
        $this->assertOutputEquals(" Starting...\n Crawling... 1 sent - 5 left\n", $output);
    }

    public function testOnSuccess()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->count()->willReturn(5);

        $monitor = new CrawlMonitor($queue->reveal(), $io);
        $monitor->onSuccess();
        // No output expected.
        $this->assertOutputEquals('', $output);
    }

    public function testOnFailure()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->count()->willReturn(5);

        $monitor = new CrawlMonitor($queue->reveal(), $io);
        $monitor->onFailure();
        // No output expected.
        $this->assertOutputEquals('', $output);
    }

    public function testOnException()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $io = new SymfonyStyle(new ArrayInput([]), $output);
        $queue = $this->prophesize(RequestQueueInterface::class);
        $queue->count()->willReturn(5);

        $monitor = new CrawlMonitor($queue->reveal(), $io);
        $monitor->onException();
        // No output expected.
        $this->assertOutputEquals('', $output);
    }

    private function assertOutputEquals($expected, StreamOutput $output)
    {
        rewind($output->getStream());
        $display = stream_get_contents($output->getStream());
        $display = str_replace(PHP_EOL, "\n", $display);
        $this->assertEquals($expected, $display);
    }
}
