<?php

namespace LastCall\Crawler\Test\Handler\Discovery;

use LastCall\Crawler\Handler\Discovery\LinkDiscoverer;
use LastCall\Crawler\Uri\NormalizerInterface;

class LinkDiscovererTest extends AbstractDiscovererTest
{
    public function getDiscoveryTests()
    {
        return [
            ['<html></html>', [], 'link'],
            ['<html><a href="/foo"></a></html>', ['http://google.com/foo'], 'link'],
            ['<html><a href="/foo"></a><a href="http://google.com/foo"></a></html>', ['http://google.com/foo'], 'link'],
        ];
    }

    public function getDiscoverer(NormalizerInterface $normalizer)
    {
        return new LinkDiscoverer($normalizer);
    }
}
