<?php

namespace LastCall\Crawler\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CrawlCommand extends CrawlerCommand
{
    public function __construct($name = 'crawl')
    {
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setDescription('Execute a crawler session on a configuration.');
        $this->setHelp('Pass in the name of a PHP file that contains the crawler configuration.');
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to a configuration file.', 'crawler.php');
        $this->addOption('reset', 'r', InputOption::VALUE_NONE, 'Run teardown/setup tasks before starting.');
        $this->addOption('chunk', 'c', InputOption::VALUE_REQUIRED, 'The concurrency to send requests at', 5);
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getConfiguration($input->getArgument('filename'));
        $this->prepareConfiguration($configuration, $input, $output);
        $dispatcher = $this->getDispatcher();
        $this->prepareDispatcher($configuration, $dispatcher, $input, $output);

        $crawler = $this->getCrawler($configuration, $dispatcher);

        if ($input->getOption('reset')) {
            $crawler->teardown();
            $crawler->setup();
        }

        $crawler
            ->start($input->getOption('chunk'))
            ->wait();
    }
}
