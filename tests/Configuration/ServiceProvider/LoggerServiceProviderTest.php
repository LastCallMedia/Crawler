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
    public function testHasLogger()
    {
        $container = new Container();
        $container['subscribers'] = function () {
            return [];
        };
        $container->register(new LoggerServiceProvider());

        $this->assertEquals(new NullLogger(), $container['logger']);
    }

    public function testAddsSubscribers()
    {
        $container = new Container();
        $container['subscribers'] = function () {
            return [];
        };
        $container->register(new LoggerServiceProvider());
        $expected = [
            'requestLogger' => new RequestLogger(new NullLogger()),
            'exceptionLogger' => new ExceptionLogger(new NullLogger()),
        ];
        $this->assertEquals($expected, $container['subscribers']);
    }

    public function testOverriddenLoggerIsUsed()
    {
        $container = new Container();
        $container['subscribers'] = function () {
            return [];
        };
        $container->register(new LoggerServiceProvider());
        $logger = $this->prophesize(LoggerInterface::class)->reveal();
        $container['logger'] = $logger;

        $expected = [
            'requestLogger' => new RequestLogger($logger),
            'exceptionLogger' => new ExceptionLogger($logger),
        ];
        $this->assertEquals($expected, $container['subscribers']);
    }
}
