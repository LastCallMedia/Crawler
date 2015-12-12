<?php


namespace LastCall\Crawler\Test\Handler\Reporting;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Handler\Reporting\PerformanceReporter;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Url\URLHandler;
use Symfony\Component\Console\Output\StreamOutput;

class PerformanceReporterTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testOutputsPerformance()
    {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $handler = new PerformanceReporter($output);

        $urlHandler = new URLHandler('foo');
        $request = new Request('GET', 'foo');
        $response = new Response(200);
        $sendingEvent = new CrawlerEvent($request, $urlHandler);
        $completeEvent = new CrawlerResponseEvent($request, $response,
            $urlHandler);

        for ($i = 0; $i < 5; $i++) {
            $this->invokeEvent($handler, CrawlerEvents::SENDING, $sendingEvent);
            $this->invokeEvent($handler, CrawlerEvents::SUCCESS,
                $completeEvent);
        }
        rewind($output->getStream());
        $this->assertRegExp('/Processed 5 in \ds \(\d+ms/',
            stream_get_contents($output->getStream()));
    }

}