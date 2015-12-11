<?php

namespace LastCall\Crawler\Test\Command;

use LastCall\Crawler\Command\ClearCommand;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Tester\CommandTester;


class ClearCommandTest extends CommandTest
{
    public function testClearCommand()
    {
        $configuration = $this->prophesize(ConfigurationInterface::class);
        $crawler = $this->prophesize(Crawler::class);
        $crawler->teardown()->shouldBeCalled();
        $crawler->setUp()->shouldBeCalled();

        $command = new ClearCommand();
        $command->setHelperSet($this->getMockHelperSet($configuration,
            $crawler));
        $tester = new CommandTester($command);
        $tester->execute(array('config' => 'test.php'));
        $this->assertContains('Cleared', $tester->getDisplay());
    }

}