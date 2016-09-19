<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use LastCall\Crawler\Handler\Discovery\StylesheetDiscoverer;
use LastCall\Crawler\Uri\NormalizerInterface;

class StylesheetDiscovererTest extends AbstractDiscovererTest
{
    public function getDiscoveryTests()
    {
        return [
            ['<html><link rel="stylesheet" href="/foo.css" /></html>', ['http://google.com/foo.css'], 'stylesheet'],
            ['<html><link rel="stylesheet" src="/foo.css" /></html>', [], null],
            ['<html><link href="/foo.css" /></html>', [], null],
            ['<html><link rel="stylesheet" href="/foo.css" /><link rel="stylesheet" href="http://google.com/foo.css" /></html>', ['http://google.com/foo.css'], 'stylesheet'],
        ];
    }

    public function getDiscoverer(NormalizerInterface $normalizer)
    {
        return new StylesheetDiscoverer($normalizer);
    }
}
