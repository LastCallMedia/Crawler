<?php


namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupTeardownCommand extends Command
{
    private $tearsDown = TRUE;
    private $setsUp = TRUE;

    public static function setup() {
        return new static(
            'setup',
            FALSE,
            TRUE,
            'Sets up the crawler for a new session.'
        );
    }

    public static function teardown() {
        return new static(
            'teardown',
            TRUE,
            FALSE,
            'Tears down the crawler.'
        );
    }

    public static function reset() {
        return new static(
            'reset',
            TRUE,
            TRUE,
            'Reset the crawler session and prepare it for a new run.'
        );
    }

    public function __construct($name, $tearsDown = TRUE, $setsUp = TRUE, $description = '')
    {
        parent::__construct($name);
        $this->tearsDown = $tearsDown;
        $this->setsUp = $setsUp;
        $this->setDescription($description);
    }

    public function configure() {
        $this->addArgument('config', InputArgument::REQUIRED, 'The path to the crawler configuration.');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        /** @var \LastCall\Crawler\Helper\CrawlerHelper $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration($input->getArgument('config'),
            $output);
        $session = $helper->getSession($config);

        if($this->tearsDown) {
            $session->onTeardown();
            $io->success('Teardown complete.');
        }
        if($this->setsUp) {
            $session->onSetup();
            $io->success('Setup complete.');
        }
    }

}