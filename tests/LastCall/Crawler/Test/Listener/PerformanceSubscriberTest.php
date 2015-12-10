<?php
/**
 * Created by PhpStorm.
 * User: rfbayliss
 * Date: 12/9/15
 * Time: 9:04 PM
 */

namespace LastCall\Crawler\Test\Listener;


use LastCall\Crawler\Listener\PerformanceSubscriber;
use Symfony\Component\Console\Output\StreamOutput;
use LastCall\Crawler\Event\CrawlerEvent;
use LastCall\Crawler\Event\CrawlerResponseEvent;

class PerformanceSubscriberTest extends \PHPUnit_Framework_TestCase {

    public function testLogsPerformance() {
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $subscriber = new PerformanceSubscriber($output);

        for($i = 0; $i < 5; $i++) {
            $sendingEvent = $this->prophesize(CrawlerEvent::class);
            $subscriber->onSending($sendingEvent->reveal());

            $completeEvent = $this->prophesize(CrawlerResponseEvent::class);
            $subscriber->onComplete($completeEvent->reveal());
        }
        rewind($output->getStream());
        $this->assertRegExp('/Processed 5 in \ds \(\d+ms/', stream_get_contents($output->getStream()));
    }
}