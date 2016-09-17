<?php

namespace LastCall\Crawler\Test\Configuration\ServiceProvider;

use LastCall\Crawler\Configuration\ServiceProvider\LoggerServiceProvider;
use LastCall\Crawler\Handler\Logging\ExceptionLogger;
use LastCall\Crawler\Handler\Logging\RequestLogger;
use Pimple\Container;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;

class LoggerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function getLoggerTests()
    {
        return [
            [new NullLogger()],
            [$this->getMock(LoggerInterface::class)],
        ];
    }

    /**
     * @dataProvider getLoggerTests
     */
    public function testHasRequestLogger($logger)
    {
        $container = new Container();
        $container->register(new LoggerServiceProvider(), [
            'logger' => $logger,
        ]);

        $expected = new RequestLogger($container['logger']);
        $this->assertEquals($expected, $container['logger.request']);
    }

    /**
     * @dataProvider getLoggerTests
     */
    public function testHasExceptionLogger($logger)
    {
        $container = new Container();
        $container->register(new LoggerServiceProvider(), [
            'logger' => $logger,
        ]);

        $expected = new ExceptionLogger($logger);
        $this->assertEquals($expected, $container['logger.exception']);
    }
}
