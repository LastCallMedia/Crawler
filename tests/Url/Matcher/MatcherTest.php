<?php


namespace LastCall\Crawler\Test\Url\Matcher;


use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Matcher;

class MatcherTest extends \PHPUnit_Framework_TestCase
{

    public function getMatchTests()
    {
        return [
            ['http://google.com', true],
            ['http://google.com/exclude/1', false],
            ['http://alta-vista.com', false],
            ['https://lastcallmedia.com/include/test', true],
        ];
    }

    /**
     * @dataProvider getMatchTests
     */
    public function testMatches($url, $expected)
    {
        $matcher = new Matcher([
            'http://google.com',
            'https://lastcallmedia.com/include'
        ], ['http://google.com/exclude']);
        $this->assertEquals($expected, $matcher->matches($url));
        $this->assertEquals($expected, $matcher->matches(new Uri($url)));
    }

    public function getFileTests()
    {
        $exts = array(
            'pdf',
            'xml',
            'png',
            'svg',
            'psd',
            'gif',
            'jpg',
            'jpeg',
            'doc',
            'docx',
            'txt'
        );

        $tests = [
            ['http://foo.com/test.png?foo=bar', true],
            ['http://foo.com/test.png#foo', true],
            ['http://foo.com/test.html#foo', false],
        ];
        foreach ($exts as $ext) {
            $tests[] = [sprintf('http://foo.com/1.%s', $ext), true];
        }

        return $tests;
    }

    /**
     * @dataProvider getFileTests
     */
    public function testFileMatches($uri, $expected)
    {
        $matcher = new Matcher();
        $this->assertEquals($expected, $matcher->matchesFile($uri));
        $this->assertEquals($expected, $matcher->matchesFile(new Uri($uri)));
    }

    public function getHtmlTests()
    {
        return [
            ['http://google.com', true],
            ['http://google.com/index.html', true],
            ['http://google.com/index.php', true],
            ['http://google.com/index.asp', true],
            ['http://google.com/index.asp#test', true],
            ['http://google.com/index.asp?foo=bar', true],
            ['http://google.com/index.cfm', true],
        ];
    }

    /**
     * @dataProvider getHtmlTests
     */
    public function testHtmlMatches($uri, $expected)
    {
        $matcher = new Matcher();
        $this->assertEquals($expected, $matcher->matchesHTML($uri));
        $this->assertEquals($expected, $matcher->matchesHTML(new Uri($uri)));
    }

}