<?php

namespace LastCall\Crawler\Test\Url;

use LastCall\Crawler\Url\Matcher;

class MatcherTest extends \PHPUnit_Framework_TestCase
{

    public function getMatchesIncludeTests()
    {
        return array(
          array('http://google.com', true),
          array('http://yahoo.com', true),
          array('http://alta-vista.com', false),
        );
    }

    /**
     * @dataProvider getMatchesIncludeTests
     */
    public function testMatchesInclude($url, $expected)
    {
        $matcher = new Matcher(['^http://google.com', '^http://yahoo.com']);
        $this->assertEquals($expected, $matcher->matchesInclude($url));
    }

    public function getMatchesExcludeTests()
    {
        return array(
          array('http://google.com', true),
          array('http://yahoo.com', true),
          array('http://alta-vista.com', false),
        );
    }

    /**
     * @dataProvider getMatchesExcludeTests
     */
    public function testMatchesExclude($url, $expected)
    {
        $matcher = new Matcher([], ['http://google.com', 'http://yahoo.com']);
        $this->assertEquals($expected, $matcher->matchesExclude($url));
    }

    public function getMatchesHTMLTests()
    {
        return array(
          array('http://google.com', true),
          array('http://google.com/index.html', true),
          array('http://google.com/index.php', true),
          array('http://google.com/index.asp', true),
          array('http://google.com/index.asp#test', true),
          array('http://google.com/index.asp?foo=bar', true),
          array('http://google.com/index.cfm', true),
        );
    }

    /**
     * @dataProvider getMatchesHTMLTests
     */
    public function testMatchesHTML($url, $expected)
    {
        $matcher = new Matcher();
        $this->assertEquals($expected, $matcher->matchesHTML($url));
    }

    public function getMatchesFileTests()
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
        $return = array(
          array('http://google.com', false),
          array('http://google.com/index.html', false),
          array('http://google.com/index.txt#test', true),
        );
        foreach ($exts as $ext) {
            $return[] = array(sprintf('http://google.com/test.%s', $ext), true);
        }

        return $return;
    }

    /**
     * @dataProvider getMatchesFileTests
     */
    public function testMatchesFile($url, $expected)
    {
        $matcher = new Matcher();
        $this->assertEquals($expected, $matcher->matchesFile($url));
    }

    public function testDefaults()
    {
        $matcher = new Matcher();
        $this->assertEquals(true,
          $matcher->matchesInclude('http://google.com'));
        $this->assertEquals(false,
          $matcher->matchesExclude('http://alta-vista.com'));
    }

    public function testAddInclusionPattern()
    {
        $matcher = new Matcher();
        $matcher->addInclusionPattern('foo');
        $this->assertTrue($matcher->matchesInclude('foo'));
    }

    public function testAddExclusionPattern()
    {
        $matcher = new Matcher();
        $matcher->addExclusionPattern('bar');
        $this->assertTrue($matcher->matchesExclude('bar'));
    }

    public function testAddingPatternResetsCompiled()
    {
        $matcher = new Matcher(['foo']);
        $this->assertFalse($matcher->matchesInclude('bar'));
        $matcher->addInclusionPattern('bar');
        $this->assertTrue($matcher->matchesInclude('foo'));
        $this->assertTrue($matcher->matchesInclude('bar'));
    }

    public function testSettingHTMLPattern()
    {
        $matcher = new Matcher(null, null, ['baz']);
        $this->assertTrue($matcher->matchesHTML('http://google.com/index.baz'));
    }

    public function testSettingFilePattern()
    {
        $matcher = new Matcher(null, null, [], ['baz']);
        $this->assertTrue($matcher->matchesFile('http://google.com/index.baz'));
    }
}