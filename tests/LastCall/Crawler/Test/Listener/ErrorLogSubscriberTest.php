<?php

namespace LastCall\Crawler\Test\Listener;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Listener\ErrorLogSubscriber;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ErrorLogSubscriberTest extends \PHPUnit_Framework_TestCase
{

    public function testLogSubscriber()
    {
        $crawler = $this->prophesize(Crawler::class)->reveal();
        $request = new Request('GET', 'http://google.com');
        $event = new CrawlerExceptionEvent($crawler, $request, new \Exception('foo'));

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->error(Argument::type('Exception'),
          ['url' => 'http://google.com'])->shouldBeCalled();
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new ErrorLogSubscriber($logger->reveal()));
        $dispatcher->dispatch(Crawler::EXCEPTION, $event);
    }
}