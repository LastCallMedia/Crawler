<?php

namespace LastCall\Crawler\Test\Fragment\Processor;

use LastCall\Crawler\Fragment\Processor\AssetProcessor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class AssetProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ProcessesTestFragments;

    public function getCssTests()
    {
        return [
            ['<html><link rel="stylesheet" href="/foobar.css" /></html>', [new Request('GET', 'https://lastcallmedia.com/foobar.css')]],
            ['<html><link rel="shortcut icon" href="/favicon.ico" /></html>', []],
            ['<html><link href="/foobar.css" /></html>', []],
        ];
    }

    /**
     * @dataProvider getCssTests
     */
    public function testProcessesCSS($html, $expectedRequests)
    {
        $processor = new AssetProcessor(
            Matcher::all()->always(),
            new Normalizer()
        );
        $event = $this->fireSuccess(
            $processor,
            new Request('GET', 'https://lastcallmedia.com'),
            new Response(200, [], $html)
        );
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }

    public function getJsTests()
    {
        return [
            ['<html><script type="text/javascript" src="/script.js"></script></html>', [new Request('GET', 'http://t.co/script.js')]],
            ['<html><script type="text/javascript" src="/script.js"/></html>', [new Request('GET', 'http://t.co/script.js')]],
            ['<html><script src="/script.js"></script></html>', []],
        ];
    }

    /**
     * @dataProvider getJsTests
     */
    public function testProcessesJs($html, $expectedRequests)
    {
        $processor = new AssetProcessor(
            Matcher::all()->always(),
            new Normalizer()
        );
        $event = $this->fireSuccess(
            $processor,
            new Request('GET', 'http://t.co'),
            new Response(200, [], $html)
        );
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }
}
