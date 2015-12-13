<?php


namespace LastCall\Crawler\Test\Handler\Module\Parser;


use GuzzleHttp\Psr7\Response;
use LastCall\Crawler\Handler\Module\Parser\CSSSelectorParser;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CSSSelectorParserTest extends \PHPUnit_Framework_TestCase
{
    public function testName() {
        $this->assertEquals('css', (new CSSSelectorParser())->getId());
    }

    public function testConvertsResponse() {
        $html = '<html><body></body></html>';
        $parser = new CSSSelectorParser();
        $dom = $parser->parseResponse(new Response(200, [], $html));
        $this->assertInstanceOf(DomCrawler::class, $dom);
        $this->assertEquals('<body></body>', $dom->html());
    }

    public function testSelectsHTML() {
        $html = '<html><body>Content<a>Foo</a></body>';
        $parser = new CSSSelectorParser();
        $a = $parser->parseNodes(new DomCrawler($html), 'a');
        $this->assertEquals('Foo', $a->text());
    }

}