<?php

namespace LastCall\Crawler\Test\Performance;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @group performance
 */
class NormalizerPerformanceTest extends \PHPUnit_Framework_TestCase
{
    public function getNormalizations()
    {
        return [
            [Normalizations::lowercaseSchemeAndHost(), 'Normalizer::lowercaseSchemeAndHost()'],
            [Normalizations::capitalizeEscaped(), 'Normalizer::capitalizeEscaped()'],
            [Normalizations::decodeUnreserved(), 'Normalizer::decodeUnreserved()'],
            [Normalizations::addTrailingSlash(), 'Normalizer::addTrailingSlash()'],
            [Normalizations::dropIndex(), 'Normalizer::dropIndex()'],
            [Normalizations::dropFragment(), 'Normalizer::dropFragment()'],
            [Normalizations::rewriteHost([
                'lastcallmedia.com' => 'www.lastcallmedia.com',
            ]), 'Normalizer::rewriteHost()'],
            [Normalizations::rewriteScheme([
                'https' => 'http',
            ]), 'Normalizer::rewriteScheme()'],
            [Normalizations::sortQuery(), 'Normalizer::sortQuery()'],
        ];
    }

    /**
     * @dataProvider getNormalizations
     */
    public function testNormalization(callable $fn, $name)
    {
        $uris = [
            new Uri('https://lastcallmedia.com/index%3a.html'),
            new Uri('https://LastCallMedia.com/foo?bar'),
            new Uri('https://lastcallmedia.com/bar?foo&bar#baz'),
        ];
        $stopwatch = new Stopwatch();
        $stopwatch->start('normalizer', $name);
        for ($i = 0; $i < 5000; ++$i) {
            foreach ($uris as $uri) {
                $fn($uri);
            }
        }
        $stopwatch->stop('normalizer');
        $event = $stopwatch->getEvent('normalizer');
        $this->logDataPoint($event);
    }

    private function logDataPoint(StopwatchEvent $event)
    {
        echo $event.PHP_EOL;
    }
}
