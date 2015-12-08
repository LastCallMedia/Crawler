<?php

namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearCommand extends ConfigurableCommand
{
    public function configure()
    {
        if(!$this->getName()) {
            $this->setName('clear');
        }
        $this->setDescription('Clear existing data.');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration($input->getArgument('config'), $output);
        $crawler = $helper->getCrawler($config);
        $crawler->teardown();
        $crawler->setUp();
        $io->success('Cleared');
    }

}