<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Console command to execute setup and teardown tasks.
 */
class SetupTeardownCommand extends CrawlerCommand
{
    private $tearsDown = true;
    private $setsUp = true;

    /**
     * Create a new instance of the command, configured for setup only.
     *
     * @return static
     */
    public static function setup()
    {
        return new static('setup', false, true,
            'Sets up the crawler for a new session.');
    }

    /**
     * Create a new instance of the command, configured for teardown only.
     *
     * @return static
     */
    public static function teardown()
    {
        return new static('teardown', true, false, 'Tears down the crawler.');
    }

    /**
     * Create a new instance of the command, configured for setup and teardown.
     *
     * @return static
     */
    public static function reset()
    {
        return new static('reset', true, true,
            'Reset the crawler session and prepare it for a new run.');
    }

    /**
     * SetupTeardownCommand constructor.
     *
     * @param null   $name
     * @param bool   $tearsDown
     * @param bool   $setsUp
     * @param string $description
     */
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

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Path to a configuration file.', 'crawler.php');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfiguration($input->getArgument('filename'));
        $this->prepareConfiguration($config, $input, $output);
        $dispatcher = new EventDispatcher();
        $this->prepareDispatcher($config, $dispatcher, $input, $output);

        $crawler = $this->getCrawler($config, $dispatcher);

        if ($this->tearsDown) {
            $crawler->teardown();
        }
        if ($this->setsUp) {
            $crawler->setup();
        }
    }
}
