<?php


namespace LastCall\Crawler\Test\Command;


use LastCall\Crawler\Command\SetupTeardownCommand;
use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Helper\CrawlerHelper;
use LastCall\Crawler\Session\SessionInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

class SetupTeardownCommandTest extends \PHPUnit_Framework_TestCase
{
    protected function getCrawlerHelper($session) {
        $helper = $this->prophesize(CrawlerHelper::class);

        $config = new Configuration('https://lastcallmedia.com');
        $helper->getConfiguration('test.php', Argument::any())->willReturn($config);
        $helper->getSession($config)->willReturn($session);
        $helper->getName()->willReturn('crawler');
        $helper->setHelperSet(Argument::any())->will(function() { return TRUE; });

        return $helper;
    }

    public function getCommands() {
        return [
            [SetupTeardownCommand::setup(), FALSE, TRUE],
            [SetupTeardownCommand::teardown(), TRUE, FALSE],
            [SetupTeardownCommand::reset(), TRUE, TRUE],
        ];
    }

    /**
     * @dataProvider getCommands
     */
    public function testCommand(Command $command, $teardownExpected, $setupExpected) {
        $session = $this->prophesize(SessionInterface::class);

        if($teardownExpected) {
            $session->onTeardown()->shouldBeCalled();
        }
        else {
            $session->onTeardown()->shouldNotBeCalled();
        }

        if($setupExpected) {
            $session->onSetup()->shouldBeCalled();
        }
        else {
            $session->onSetup()->shouldNotBeCalled();
        }

        $command->setHelperSet(
            new HelperSet([$this->getCrawlerHelper($session)->reveal()])
        );

        $tester = new CommandTester($command);
        $tester->execute(['config' => 'test.php']);

        if($teardownExpected) {
            $this->assertContains('Teardown complete', $tester->getDisplay());
        }
        if($setupExpected) {
            $this->assertContains('Setup complete', $tester->getDisplay());
        }
    }


}