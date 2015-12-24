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
            [Normalizer::normalizeCase(), 'Normalizer::normalizeCase()'],
            [
                Normalizer::preferredDomainMap(['lastcallmedia.com' => 'foo.com']),
                'Normalizer::preferredDomainMap()',
            ],
            [Normalizer::stripFragment(), 'Normalizer::stripFragment()'],
        ];
    }

    /**
     * @dataProvider getNormalizerPasses
     */
    public function testNormalization(callable $fn, $name)
    {
        $uris = [
            new Uri('https://lastcallmedia.com/index.html'),
            new Uri('https://LastCallMedia.com/foo'),
            new Uri('https://lastcallmedia.com/bar#baz'),
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
