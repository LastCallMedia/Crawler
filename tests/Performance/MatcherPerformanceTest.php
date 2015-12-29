<?php

namespace LastCall\Crawler\Test\Performance;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\MatcherAssert;

/**
 * @group performance
 */
class MatcherPerformanceTest extends \PHPUnit_Framework_TestCase
{
    public function getMatcherPasses()
    {
        return [
            [MatcherAssert::schemeIs('http'), 'MatcherAssert::schemeIs(single)'],
            [MatcherAssert::schemeIs(['http', 'https']), 'MatcherAssert::schemeIs(multiple)'],
            [MatcherAssert::schemeMatches('/http/'), 'MatcherAssert::schemeMatches(single)'],
            [MatcherAssert::schemeMatches(['/http/', '/https']), 'MatcherAssert::schemeMatches(multiple)'],
            [MatcherAssert::hostIs('lastcallmedia.com'), 'MatcherAssert::hostIs(single)'],
            [MatcherAssert::hostIs(['example.com', 'lastcallmedia.com']), 'MatcherAssert::hostIs(multiple)'],
            [MatcherAssert::hostMatches('/lastcallmedia.com/'), 'MatcherAssert::hostMatches(single)'],
            [MatcherAssert::hostMatches(['/example\.com/', '/lastcallmedia\.com/']), 'MatcherAssert::hostMatches(single)'],
            [MatcherAssert::portIs(8081), 'MatcherAssert::portIs'],
            [MatcherAssert::portIn(5000, 10000), 'MatcherAssert::portIn'],
            [MatcherAssert::pathIs('foo'), 'MatcherAssert::pathIs(single)'],
            [MatcherAssert::pathIs(['bar', 'foo']), 'MatcherAssert::pathIs(multiple)'],
            [MatcherAssert::pathMatches('/foo\.html/'), 'MatcherAssert::pathMatches(single)'],
            [MatcherAssert::pathMatches(['/bar/', '/foo\.html/']), 'MatcherAssert::pathMatches(multiple)'],
            [MatcherAssert::pathExtensionIs('html'), 'MatcherAssert::pathExtensionIs(single)'],
            [MatcherAssert::pathExtensionIs(['php', 'html']), 'MatcherAssert::pathExtensionIs(multiple)'],
            [MatcherAssert::queryIs('bar'), 'MatcherAssert::queryIs(single)'],
            [MatcherAssert::queryIs(['boo', 'bar']), 'MatcherAssert::queryIs(multiple)'],
            [MatcherAssert::queryMatches('/bar/'), 'MatcherAssert::queryMatches(single)'],
            [MatcherAssert::queryMatches(['/boo/', '/bar/']), 'MatcherAssert::queryMatches(multiple)'],
            [MatcherAssert::fragmentIs('baz'), 'MatcherAssert::fragmentIs(single)'],
            [MatcherAssert::fragmentIs(['bar', 'baz']), 'MatcherAssert::fragmentIs(multiple)'],
            [MatcherAssert::fragmentMatches('/baz/'), 'MatcherAssert::fragmentMatches(single)'],
            [MatcherAssert::fragmentMatches(['/bar/', '/baz/']), 'MatcherAssert::fragmentMatches(multiple)'],
        ];
    }

    public function inverseMatcherPasses()
    {
        $passes = $this->getMatcherPasses();
        foreach ($passes as &$pass) {
            $pass[0] = MatcherAssert::not($pass[0]);
            $pass[1] = substr($pass[1], 0, -1).', negative)';
        }

        return array_merge($this->getMatcherPasses(), $passes);
    }

    /**
     * @dataProvider inverseMatcherPasses
     */
    public function testMatcherPass(callable $pass, $name)
    {
        $uri = new Uri('https://lastcallmedia.com:8081/foo.html?bar#baz');
        $time = microtime(true);
        for ($i = 0; $i < 5000; ++$i) {
            $pass($uri);
        }

        $duration = microtime(true) - $time;
        echo sprintf('%s: %dms', $name, $this->formatMs($duration)).PHP_EOL;
    }

    public function formatMs($time)
    {
        return round($time * 1000);
    }
}
