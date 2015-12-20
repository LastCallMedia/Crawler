<?php


namespace LastCall\Crawler\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupTeardownCommand extends Command
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
        parent::__construct($name);
        $this->tearsDown = $tearsDown;
        $this->setsUp = $setsUp;
        $this->setDescription($description);
    }

    public function configure()
    {
        $this->addArgument('config', InputArgument::REQUIRED,
            'The path to the crawler configuration.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var \LastCall\Crawler\Helper\CrawlerHelperInterface $helper */
        $helper = $this->getHelper('crawler');
        $config = $helper->getConfiguration();
        $session = $helper->getSession($config);

        if ($this->tearsDown) {
            $session->onTeardown();
            $io->success('Teardown complete.');
        }
        if ($this->setsUp) {
            $session->onSetup();
            $io->success('Setup complete.');
        }
    }

}