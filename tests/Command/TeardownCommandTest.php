<?php

namespace LastCall\Crawler\Test\Command;


use LastCall\Crawler\Command\TeardownCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Tester\CommandTester;

class TeardownCommandTest extends CommandTest
{

    public function testCommandExecutesTeardown()
    {
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $crawler = $this->prophesize(Crawler::class);
        $crawler->teardown()->shouldBeCalled();

        $command = new TeardownCommand();
        $command->setHelperSet($this->getMockHelperSet($configuration,
            $crawler));
        $tester = new CommandTester($command);
        $tester->execute(array('config' => 'test.php'));
        $this->assertContains('Teardown complete', $tester->getDisplay());
    }
}