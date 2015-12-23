<?php


namespace LastCall\Crawler\Test\Fragment\Parser;


use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Fragment\Parser\XPathParser;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class XpathSelectorTest extends \PHPUnit_Framework_TestCase
{

    public function testName()
    {
        $this->assertEquals('xpath', (new XPathParser())->getId());
    }

    public function testConvertsResponse()
    {
        $html = '<html><body></body></html>';
        $parser = new XPathParser();
        $dom = $parser->prepareResponse(new Response(200, [], $html));
        $this->assertInstanceOf(DomCrawler::class, $dom);
        $this->assertEquals('<body></body>', $dom->html());
    }

    public function testSelectsHtml()
    {
        $html = '<html><body>Content<a>Foo</a></body>';
        $parser = new XPathParser();
        $a = $parser->parseFragments(new DomCrawler($html),
            'descendant-or-self::a');
        $this->assertEquals('Foo', $a->text());
    }

}