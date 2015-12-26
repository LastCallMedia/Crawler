<?php

namespace LastCall\Crawler\Test\Fragment\Processor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Event\CrawlerResponseEvent;
use LastCall\Crawler\Fragment\Processor\LinkProcessor;
use LastCall\Crawler\Uri\Matcher;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class LinkProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscribesToRightMethod()
    {
        $matcher = Matcher::create()->always();
        $normalizer = new Normalizer();

        $processor = new LinkProcessor($matcher, $normalizer);
        $methods = $processor->getSubscribedMethods();
        $this->assertCount(1, $methods);
        /** @var \LastCall\Crawler\Fragment\FragmentSubscription $method */
        $method = reset($methods);
        $this->assertEquals('xpath', $method->getParserId());
        $this->assertEquals('descendant-or-self::a[@href]',
            $method->getSelector());
        $this->assertEquals([$processor, 'processLinks'],
            $method->getCallable());
    }

    public function getInputs()
    {
        $inputs = [
            [
                '<html><a href="/foo"></a></html>',
                ['https://lastcallmedia.com/foo'],
            ],
            [
                '<html><a href="https://lastcallmedia.com/bar">Test</a></html>',
                ['https://lastcallmedia.com/bar'],
            ],
        ];

        return $inputs;
    }

    /**
     * @dataProvider getInputs
     */
    public function testProcessLinks($html, $expected)
    {
        $request = new Request('GET', 'https://lastcallmedia.com');
        $response = new Response();
        $event = new CrawlerResponseEvent($request, $response);

        $links = (new DomCrawler($html))->filterXPath('descendant-or-self::a[@href]');

        $matcher = Matcher::create()->always();
        $normalizer = new Normalizer();
        $processor = new LinkProcessor($matcher, $normalizer);
        $processor->processLinks($event, $links);

        $added = [];
        foreach ($event->getAdditionalRequests() as $addedRequest) {
            $added[] = (string) $addedRequest->getUri();
        }
        $this->assertEquals($expected, $added);
    }
}
