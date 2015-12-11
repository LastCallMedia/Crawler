<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Command\SetupCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Tester\CommandTester;


class SetupCommandTest extends CommandTest
{
    public function testSetupCommand()
    {
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $crawler = $this->prophesize(Crawler::class);
        $crawler->setUp()->shouldBeCalled();

        $command = new SetupCommand();
        $command->setHelperSet($this->getMockHelperSet($configuration,
            $crawler));
        $tester = new CommandTester($command);
        $tester->execute(array('config' => 'test.php'));
        $this->assertContains('Setup complete', $tester->getDisplay());
    }

}