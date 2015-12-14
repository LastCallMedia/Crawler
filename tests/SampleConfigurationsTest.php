<?php


namespace LastCall\Crawler\Test;

use LastCall\Crawler\Configuration\ConfigurationInterface;
use Symfony\Component\Console\Output\StreamOutput;

class SampleConfigurationsTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleConfig()
    {
        $output = new StreamOutput(fopen('php://memory', 'r'));
        $config = require_once __DIR__ . '/../docs/sample.php';
        $this->assertInstanceOf(ConfigurationInterface::class, $config);
    }

    public function testSubclassConfig()
    {
        $output = new StreamOutput(fopen('php://memory', 'r'));
        $config = require_once __DIR__ . '/../docs/SampleSubclassConfiguration.php';
        $this->assertInstanceOf(ConfigurationInterface::class, $config);
    }

}