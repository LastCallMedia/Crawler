<?php

namespace LastCall\Crawler\Command;

use Symfony\Component\Console\Input\InputInterface;
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
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getConfiguration($input->getArgument('filename'));
        $this->prepareConfiguration($configuration, $input, $output);

        $this
            ->getCrawler($configuration)
            ->start(5)
            ->wait();
    }
}
