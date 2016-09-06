<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Session\Session;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle($input, $output);

        $config = $this->getConfiguration($input->getArgument('filename'));
        $this->prepareConfiguration($config, $input, $output);
        $session = $this->getSession($config, $this->getDispatcher());

        if ($this->tearsDown) {
            $session->teardown();
            $io->success('Teardown complete.');
        }
        if ($this->setsUp) {
            $session->setup();
            $io->success('Setup complete.');
        }
    }
}
