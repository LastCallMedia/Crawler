<?php

namespace LastCall\Crawler\Test\Helper;

use GuzzleHttp\Psr7\Request;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Crawler;
use LastCall\Crawler\Helper\InputAwareCrawlerHelper;
use LastCall\Crawler\Reporter\ReporterInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InputAwareCrawlerHelperTest extends \PHPUnit_Framework_TestCase
{

    private function getInput($filename)
    {
        $definition = new InputDefinition([
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED,
                'config.php')
        ]);

        return new ArrayInput(['--config' => $filename], $definition);
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\LogicException
     * @expectedExceptionMessage Unable to load configuration - input has not
     *                           been set.
     */
    public function testGetConfigurationWithoutInput()
    {
        $helper = new InputAwareCrawlerHelper();
        $helper->getConfiguration();
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\InvalidOptionException
     * @expectedExceptionMessage Configuration file
     *                           /some/config/that/doesnt/exist.php does not
     *                           exist
     */
    public function testGetInvalidConfigurationFile()
    {
        $helper = new InputAwareCrawlerHelper();
        $helper->setInput($this->getInput('/some/config/that/doesnt/exist.php'));
        $helper->getConfiguration();
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Configuration was not returned.
     */
    public function testGetNotReturnedConfiguration()
    {
        $file = $this->writeTempConfig("<?php\n");

        $helper = new InputAwareCrawlerHelper();
        $helper->setInput($this->getInput($file));
        $helper->getConfiguration();
    }

    /**
     * @expectedException \Symfony\Component\Console\Exception\RuntimeException
     * @expectedExceptionMessage Configuration must implement
     *                           LastCall\Crawler\Configuration\ConfigurationInterface
     */
    public function testGetInvalidConfiguration()
    {
        $file = $this->writeTempConfig("<?php\nreturn new stdClass();");
        $helper = new InputAwareCrawlerHelper();
        $helper->setInput($this->getInput($file));
        $helper->getConfiguration();
    }

    public function testGetConfiguration()
    {
        $file = $this->writeTempConfig(sprintf("<?php\nreturn new %s('http://google.com');",
            Configuration::class));
        $helper = new InputAwareCrawlerHelper();
        $helper->setInput($this->getInput($file));
        $config = $helper->getConfiguration();
        $this->assertInstanceOf(ConfigurationInterface::class, $config);
        $this->assertEquals('http://google.com', $config->getBaseUrl());
    }

    public function testGetSessionWithReporter()
    {
        $config = new Configuration('https://lastcallmedia.com');
        $dispatcher = new EventDispatcher();
        $reporter = $this->prophesize(ReporterInterface::class);

        $helper = new InputAwareCrawlerHelper($dispatcher);
        $session = $helper->getSession($config, $reporter->reveal());
        $reporter->report(Argument::type('array'))->shouldBeCalled();
        $session->onRequestSending(new Request('GET',
            'https://lastcallmedia.com'));
    }

    public function testGetCrawler()
    {
        $helper = new InputAwareCrawlerHelper();
        $config = new Configuration('https://lastcallmedia.com');
        $session = $helper->getSession($config);
        $this->assertInstanceOf(Crawler::class,
            $helper->getCrawler($config, $session));
    }

    private function writeTempConfig($code)
    {
        $file = tempnam(sys_get_temp_dir(), 'phpunit-crawler-helper-test');
        file_put_contents($file, $code);

        return $file;
    }
}