<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use LastCall\Crawler\Handler\Discovery\ImageDiscoverer;
use LastCall\Crawler\Uri\NormalizerInterface;

class ImageDiscovererTest extends AbstractDiscovererTest
{
    public function getDiscoveryTests()
    {
        return [
            ['<html></html>', [], null],
            ['<html><img src="/foo.jpg" /></html>', ['http://google.com/foo.jpg'], 'image'],
            ['<html><img href="/foo.jpg" /></html>', [], null],
            ['<html><img src="/foo.jpg" /><img src="http://google.com/foo.jpg" /></html>', ['http://google.com/foo.jpg'], 'image'],
        ];
    }

    public function getDiscoverer(NormalizerInterface $normalizer)
    {
        return new ImageDiscoverer($normalizer);
    }
}
