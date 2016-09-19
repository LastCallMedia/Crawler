<?php


namespace LastCall\Crawler\Test\Handler\Discovery;

use LastCall\Crawler\Handler\Discovery\ScriptDiscoverer;
use LastCall\Crawler\Uri\NormalizerInterface;


class ScriptDiscovererTest extends AbstractDiscovererTest
{
    public function getDiscoveryTests()
    {
        return [
            ['<html><script type="text/javascript" src="/foo.js"></script></html>', ['http://google.com/foo.js'], 'script'],
            ['<html><script src="/foo.js"></script></html>', [], null],
            ['<html><script type="text/javascript" href="/foo.js"></script></html>', [], null],
            ['<html><script type="text/javascript" href="/foo.js"></script><script type="text/javascript" src="http://google.com/foo.css"></script></html>', ['http://google.com/foo.css'], 'script'],
        ];
    }

    public function getDiscoverer(NormalizerInterface $normalizer)
    {
        return new ScriptDiscoverer($normalizer);
    }

}