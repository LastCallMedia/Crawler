<?php

namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupCommand extends Command
{
    public function configure()
    {
        if (!$this->getName()) {
            $this->setName('setup');
        }
        $this->addArgument('config', InputArgument::REQUIRED,
            'The path to the crawler configuration.');
        $this->setDescription('Set up any dependencies before the crawler is run');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration($input->getArgument('config'),
            $output);
        $crawler = $helper->getCrawler($config, false);
        $crawler->setup();
        $io = new SymfonyStyle($input, $output);
        $io->success('Setup complete');
    }

}