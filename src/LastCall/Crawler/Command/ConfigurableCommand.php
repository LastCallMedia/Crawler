<?php

namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ConfigurableCommand extends Command
{
    public function configure() {
        $this->addArgument('config', InputArgument::REQUIRED, 'A PHP file containing the crawler configuration');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return \LastCall\Crawler\Configuration\ConfigurationInterface
     */
    protected function getConfiguration(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('config');
        return $helper->getConfiguration($input->getArgument('config'), $output);
    }

}