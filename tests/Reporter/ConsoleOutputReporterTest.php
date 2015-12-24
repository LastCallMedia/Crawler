<?php

namespace LastCall\Crawler\Test\Reporter;

use LastCall\Crawler\Reporter\ConsoleOutputReporter;
use Symfony\Component\Console\Output\StreamOutput;

class ConsoleOutputReporterTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $reporter = new ConsoleOutputReporter($output);
        $reporter->report(['sent' => 1, 'remaining' => 2]);
        rewind($output->getStream());
        $display = stream_get_contents($output->getStream());
        $this->assertEquals(" Starting\n Crawling 1 sent - 2 left\n", $display);
    }
}
