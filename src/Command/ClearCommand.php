<?php

namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clear crawler command - executes setup and teardown.
 */
class ClearCommand extends Command
{

    public function configure()
    {
        if (!$this->getName()) {
            $this->setName('clear');
        }
        $this->addArgument('config', InputArgument::REQUIRED,
            'The path to the crawler configuration.');
        $this->setDescription('Clear existing data.');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration($input->getArgument('config'),
            $output);
        $session = $helper->getSession($config);
        $crawler = $helper->getCrawler($session, $config);
        $crawler->teardown();
        $crawler->setUp();
        $io->success('Cleared');
    }

}