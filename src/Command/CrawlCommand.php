<?php

namespace LastCall\Crawler\Command;

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
        $this->addOption('profile', 'p', InputOption::VALUE_NONE,
            'Whether to profile the run');
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
        $crawler = $helper->getCrawler($configuration,
            $input->getOption('profile'));

        if ($input->getOption('reset')) {
            $crawler->teardown();
            $crawler->setUp();
        }

        $chunk = $input->getOption('chunk');
        $seed = $input->getArgument('seed');
        $promise = $crawler->start($chunk, $seed);

        $promise->wait();
        $io = new SymfonyStyle($input, $output);
        $io->success('Crawling complete.');

        if ($input->getOption('profile')) {
            $this->getHelper('profiler')->renderProfile($io);
        }
    }
}