<?php


namespace LastCall\Crawler\Test\Handler\Logging;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerExceptionEvent;
use LastCall\Crawler\Handler\Logging\ExceptionLoggingHandler;
use LastCall\Crawler\Test\Handler\HandlerTestTrait;
use LastCall\Crawler\Url\URLHandler;
use Psr\Log\LoggerInterface;


class ExceptionLoggingHandlerTest extends \PHPUnit_Framework_TestCase
{
    use HandlerTestTrait;

    public function testLogsException()
    {
        $request = new Request('GET', 'bar');
        $exception = new \Exception('foo');

        $logger = $this->prophesize(LoggerInterface::class);

        $handler = new ExceptionLoggingHandler($logger->reveal());
        $event = new CrawlerExceptionEvent($request, null, $exception,
            new URLHandler('foo'));
        $this->invokeEvent($handler, CrawlerEvents::EXCEPTION, $event);

        $logger->critical($exception, [
            'exception' => $exception,
            'url' => 'bar',
        ])->shouldHaveBeenCalled();
    }

}