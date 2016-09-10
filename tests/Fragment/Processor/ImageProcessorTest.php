<?php

namespace LastCall\Crawler\Test\Fragment\Processor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Fragment\Processor\ImageProcessor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;

class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ProcessesTestFragments;

    public function getImageTests()
    {
        return [
            ['<html><img src="/foo.jpg" /></html>', [new Request('GET', 'https://lastcallmedia.com/foo.jpg')]],
            ['<html><img src="https://google.com/foo.jpg" /></html>', [new Request('GET', 'https://google.com/foo.jpg')]],
            ['<html><img /></html>', []],
        ];
    }

    /**
     * @dataProvider getImageTests
     */
    public function testParsesImages($html, $expectedRequests)
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $response = new Response(200, [], $html);
        $processor = new ImageProcessor(Matcher::all()->always(), new Normalizer());

        $event = $this->fireSuccess($processor, $request, $response);
        $this->assertEquals($expectedRequests, $event->getAdditionalRequests());
    }
}
