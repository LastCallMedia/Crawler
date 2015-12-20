<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Reporter\ConsoleOutputReporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlCommand extends Command
{

    public function configure()
    {
        if (!$this->getName()) {
            $this->setName('crawl');
        }

        $this->setDescription('Work through items in the request queue.');
        $this->addArgument('config', InputArgument::REQUIRED,
            'The path to the crawler configuration.');
        $this->addOption('chunk', null, InputOption::VALUE_OPTIONAL,
            'The amount of items to process.', 5);
        $this->addOption('reset', 'r', InputOption::VALUE_NONE,
            'Reset the migration prior to running');
        parent::configure();
        $this->addArgument('seed', InputArgument::OPTIONAL,
            'The URL to pre-seed the crawler with');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $configuration = $helper->getConfiguration($input->getArgument('config'),
            $output);

        $reporter = new ConsoleOutputReporter($output);
        $session = $helper->getSession($configuration, $reporter);

        $crawler = $helper->getCrawler($session, $configuration);

        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('reset')) {
            $crawler->teardown();
            $crawler->setUp();
            $io->success('Resetting');
        }

        $chunk = $input->getOption('chunk');
        $seed = $input->getArgument('seed');
        $promise = $crawler->start($chunk, $seed);

        $promise->wait();
        $io->success('Crawling complete.');
    }
}