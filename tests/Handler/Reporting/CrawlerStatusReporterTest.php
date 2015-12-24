<?php

namespace LastCall\Crawler\Test\Handler\Reporting;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Handler\Reporting\CrawlerStatusReporter;
use LastCall\Crawler\Queue\ArrayRequestQueue;
use LastCall\Crawler\Reporter\ReporterInterface;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;

class CrawlerStatusReporterTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    private function getEvent()
    {
        $request = new Request('GET', 'https://lastcallmedia.com');

        return new CrawlerEvent($request);
    }

    public function getReportTests()
    {
        return [
            [[CrawlerEvents::SENDING, CrawlerEvents::SENDING], ['sent' => 2]],
            [
                [CrawlerEvents::SENDING, CrawlerEvents::SUCCESS],
                ['sent' => 1, 'success' => 1],
            ],
            [
                [CrawlerEvents::SENDING, CrawlerEvents::FAILURE],
                ['sent' => 1, 'failure' => 1],
            ],
            [
                [CrawlerEvents::SENDING, CrawlerEvents::EXCEPTION],
                ['sent' => 1, 'exception' => 1],
            ],
        ];
    }

    /**
     * @dataProvider getReportTests
     */
    public function testReportsOnSuccess($invocations, $stats)
    {
        $target = $this->prophesize(ReporterInterface::class);
        $handler = new CrawlerStatusReporter(new ArrayRequestQueue(),
            [$target->reveal()]);
        $event = $this->getEvent();
        foreach ($invocations as $invocation) {
            $this->invokeEvent($handler, $invocation, $event);
        }
        $stats += [
            'sent' => 0,
            'success' => 0,
            'failure' => 0,
            'exception' => 0,
            'remaining' => 0,
        ];
        $target->report($stats)->shouldHaveBeenCalled();
    }
}
