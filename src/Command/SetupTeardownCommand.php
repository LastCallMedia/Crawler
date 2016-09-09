<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SetupTeardownCommand extends CrawlerCommand
{
    private $tearsDown = true;
    private $setsUp = true;

    public static function setup()
    {
        return new static('setup', false, true,
            'Sets up the crawler for a new session.');
    }

    public static function teardown()
    {
        return new static('teardown', true, false, 'Tears down the crawler.');
    }

    public static function reset()
    {
        return new static('reset', true, true,
            'Reset the crawler session and prepare it for a new run.');
    }

    public function __construct(
        $name,
        $tearsDown = true,
        $setsUp = true,
        $description = ''
    ) {
        $this->setDescription($description);
        parent::__construct($name);
        $this->tearsDown = $tearsDown;
        $this->setsUp = $setsUp;
    }

    public function configure()
    {
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to a configuration file.', 'crawler.php');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfiguration($input->getArgument('filename'));
        $this->prepareConfiguration($config, $input, $output);
        $dispatcher = new EventDispatcher();
        $this->prepareDispatcher($config, $dispatcher, $input, $output);

        $crawler = new Crawler($dispatcher, $config->getClient(), $config->getQueue());

        if ($this->tearsDown) {
            $crawler->teardown();
        }
        if ($this->setsUp) {
            $crawler->setup();
        }
    }
}
