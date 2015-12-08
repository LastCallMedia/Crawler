<?php

namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TeardownCommand extends ConfigurableCommand
{
    public function configure()
    {
        if(!$this->getName()) {
            $this->setName('teardown');
        }
        $this->setDescription('Tear down any dependencies after the crawler is run');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration($input->getArgument('config'), $output);
        $crawler = $helper->getCrawler($config);
        $crawler->teardown();
        $io = new SymfonyStyle($input, $output);
        $io->success('Teardown complete');
    }

}