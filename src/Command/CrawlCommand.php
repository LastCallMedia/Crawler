<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Handler\CrawlMonitor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $session = $this->getSession($configuration, $dispatcher);

        if ($input->getOption('reset')) {
            $session->teardown();
            $session->setup();
        }

        $this
            ->getCrawler($configuration, $session)
            ->start($input->getOption('chunk'))
            ->wait();
    }
}
