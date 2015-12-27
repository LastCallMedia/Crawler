<?php

namespace LastCall\Crawler\Test\Performance;

use GuzzleHttp\Psr7\Uri;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @group performance
 */
class NormalizerPerformanceTest extends \PHPUnit_Framework_TestCase
{
    public function getNormalizerPasses()
    {
        return [
            [Normalizer::lowercaseSchemeAndHost(), 'Normalizer::lowercaseSchemeAndHost()'],
            [Normalizer::capitalizeEscaped(), 'Normalizer::capitalizeEscaped()'],
            [Normalizer::decodeUnreserved(), 'Normalizer::decodeUnreserved()'],
            [Normalizer::addTrailingSlash(), 'Normalizer::addTrailingSlash()'],
            [Normalizer::dropIndex(), 'Normalizer::dropIndex()'],
            [Normalizer::dropFragment(), 'Normalizer::dropFragment()'],
            [Normalizer::rewriteHost([
                'lastcallmedia.com' => 'www.lastcallmedia.com',
            ]), 'Normalizer::rewriteHost()'],
            [Normalizer::rewriteScheme([
                'https' => 'http',
            ]), 'Normalizer::rewriteScheme()'],
            [Normalizer::sortQuery(), 'Normalizer::sortQuery()'],
        ];
    }

    /**
     * @dataProvider getNormalizerPasses
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
