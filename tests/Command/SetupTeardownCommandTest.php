<?php


namespace LastCall\Crawler\Test\Command;


use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Helper\PreloadedCrawlerHelper;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

class SetupTeardownCommandTest extends \PHPUnit_Framework_TestCase
{
    protected function getCrawlerHelper(
        callable $setupListener,
        callable $teardownListener
    ) {
        $config = new Configuration('https://lastcallmedia.com');
        $config->addListener(CrawlerEvents::SETUP, $setupListener);
        $config->addListener(CrawlerEvents::TEARDOWN, $teardownListener);

        return new PreloadedCrawlerHelper($config);
    }

    public function getCommands()
    {
        return [
            [SetupTeardownCommand::setup(), false, true],
            [SetupTeardownCommand::teardown(), true, false],
            [SetupTeardownCommand::reset(), true, true],
        ];
    }

    /**
     * @dataProvider getCommands
     */
    public function testCommand(
        Command $command,
        $teardownExpected,
        $setupExpected
    ) {
        $setupCalled = $teardownCalled = false;
        $setupListener = function () use (&$setupCalled) {
            $setupCalled = true;
        };
        $teardownListener = function () use (&$teardownCalled) {
            $teardownCalled = true;
        };
        $config = new Configuration('https://lastcallmedia.com');
        $config->addListener(CrawlerEvents::SETUP, $setupListener);
        $config->addListener(CrawlerEvents::TEARDOWN, $teardownListener);

        $command->setHelperSet(new HelperSet([
            new PreloadedCrawlerHelper($config)
        ]));

        $tester = new CommandTester($command);
        $tester->execute(['config' => 'test.php']);

        $this->assertEquals($teardownExpected, $teardownCalled);
        $this->assertEquals($setupExpected, $setupCalled);

        if ($teardownExpected) {
            $this->assertContains('Teardown complete', $tester->getDisplay());
        }
        if ($setupExpected) {
            $this->assertContains('Setup complete', $tester->getDisplay());
        }
    }


}