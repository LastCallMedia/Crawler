<?php

namespace LastCall\Crawler\Test;

use LastCall\Crawler\Configuration\ConfigurationInterface;

class SampleConfigurationsTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleConfig()
    {
        $config = require __DIR__.'/../docs/sample.php';
        $this->assertInstanceOf(ConfigurationInterface::class, $config);
    }
}
