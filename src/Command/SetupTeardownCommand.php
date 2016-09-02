<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Configuration\Factory\ConfigurationFactoryInterface;
use LastCall\Crawler\Session\Session;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SetupTeardownCommand extends Command
{
    private $tearsDown = true;
    private $setsUp = true;
    private $factory;

    public static function setup(ConfigurationFactoryInterface $factory)
    {
        return new static('setup', $factory, false, true,
            'Sets up the crawler for a new session.');
    }

    public static function teardown(ConfigurationFactoryInterface $factory)
    {
        return new static('teardown', $factory, true, false, 'Tears down the crawler.');
    }

    public static function reset(ConfigurationFactoryInterface $factory)
    {
        return new static('reset', $factory, true, true,
            'Reset the crawler session and prepare it for a new run.');
    }

    public function __construct(
        $name,
        ConfigurationFactoryInterface $factory,
        $tearsDown = true,
        $setsUp = true,
        $description = ''
    ) {
        $this->factory = $factory;
        parent::__construct($name);
        $this->tearsDown = $tearsDown;
        $this->setsUp = $setsUp;
        $this->setDescription($description);
    }

    public function configure()
    {
        $this->factory->configureInput($this->getDefinition());
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->factory->getConfiguration($input);
        $dispatcher = new EventDispatcher();
        $session = Session::createFromConfig($config, $dispatcher);

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
