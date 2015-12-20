<?php

namespace LastCall\Crawler\Test\Helper;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Helper\CrawlerHelper;
use LastCall\Crawler\Helper\ProfilerHelper;
use LastCall\Crawler\Reporter\ReporterInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CrawlerHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     * @expectedException File does not exist:
     *                    /some/config/that/doesnt/exist.php
     */
    public function testGetInvalidConfigurationFile()
    {
        $helper = new CrawlerHelper();
        $helper->getConfiguration('/some/config/that/doesnt/exist.php',
            new NullOutput());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Configuration was not returned.
     */
    public function testGetNotReturnedConfiguration()
    {
        $file = $this->writeTempConfig("<?php\n");

        $helper = new CrawlerHelper();
        $helper->getConfiguration($file, new NullOutput());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Configuration must implement
     *                           LastCall\Crawler\Configuration\ConfigurationInterface
     */
    public function testGetInvalidConfiguration()
    {
        $file = $this->writeTempConfig("<?php\nreturn new stdClass();");
        $helper = new CrawlerHelper();
        $helper->getConfiguration($file, new NullOutput());
    }

    public function testGetConfiguration()
    {
        $file = $this->writeTempConfig(sprintf("<?php\nreturn new %s('http://google.com');",
            Configuration::class));
        $helper = new CrawlerHelper();
        $output = new NullOutput();
        $config = $helper->getConfiguration($file, $output);
        $this->assertInstanceOf(ConfigurationInterface::class, $config);
        $this->assertEquals('http://google.com', $config->getBaseUrl());
    }

    public function testGetSessionWithReporter()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $dispatcher = new EventDispatcher();
        $reporter = $this->prophesize(ReporterInterface::class);

        $helper = new CrawlerHelper($dispatcher);
        $session = $helper->getSession($config, $reporter->reveal());
        $reporter->report(Argument::type('array'))->shouldBeCalled();
        $session->onRequestSending(new Request('GET',
            'https://lastcallmedia.com'));
    }

    public function testGetCrawler()
    {
        $helper = new CrawlerHelper();
        $config = new Configuration('https://lastcallmedia.com');
        $session = $helper->getSession($config);
        $this->assertInstanceOf(Crawler::class,
            $helper->getCrawler($session, $config));
    }

    private function writeTempConfig($code)
    {
        $file = tempnam(sys_get_temp_dir(), 'phpunit-crawler-helper-test');
        file_put_contents($file, $code);

        return $file;
    }
}