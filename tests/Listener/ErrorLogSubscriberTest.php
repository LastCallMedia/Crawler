<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Listener\ErrorLogSubscriber;
use Prophecy\Argument;

class ErrorLogSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testLogSubscriber()
    {
        $request = new Request('GET', 'http://google.com');
        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->error(Argument::type('Exception'),
            ['url' => 'http://google.com'])->shouldBeCalled();

        $event = $this->prophesize(CrawlerExceptionEvent::class);
        $event->getRequest()->willReturn($request);
        $event->getException()->willReturn(new \Exception('foo'));

        $subscriber = new ErrorLogSubscriber($logger->reveal());
        $subscriber->onRequestException($event->reveal());
    }
}