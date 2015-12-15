<?php

namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TeardownCommand extends Command
{
    public function configure()
    {
        if (!$this->getName()) {
            $this->setName('teardown');
        }
        $this->addArgument('config', InputArgument::REQUIRED,
            'The path to the crawler configuration.');
        $this->setDescription('Tear down any dependencies after the crawler is run');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration($input->getArgument('config'),
            $output);
        $session = $helper->getSession($config, false);
        $crawler = $helper->getCrawler($session, $config);
        $crawler->teardown();
        $io = new SymfonyStyle($input, $output);
        $io->success('Teardown complete');
    }

}