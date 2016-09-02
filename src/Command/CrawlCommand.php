<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Reporter\ConsoleOutputReporter;
use LastCall\Crawler\Configuration\Factory\ConfigurationFactoryInterface;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use LastCall\Crawler\Handler\Reporting\CrawlerStatusReporter;


class CrawlCommand extends Command
{
    /**
     * @var \LastCall\LinkChecker\ConfigurationFactory\ConfigurationFactoryInterface
     */
    private $factory;

    public function __construct(ConfigurationFactoryInterface $factory) {
        $this->factory = $factory;
        parent::__construct(NULL);
    }

    public function configure() {
        $this->setName($this->factory->getName());
        $this->setDescription($this->factory->getDescription());
        $this->setHelp($this->factory->getHelp());
        $this->factory->configureInput($this->getDefinition());
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $configuration = $this->factory->getConfiguration($input);

        if($configuration instanceof OutputAwareInterface) {
            $configuration->setOutput($output);
        }

        $dispatcher = new EventDispatcher();
        // Set up the reporter.
        $dispatcher->addSubscriber(
            new CrawlerStatusReporter(
                $configuration->getQueue(),
                [new ConsoleOutputReporter($output)]
            )
        );

        $session = Session::createFromConfig($configuration, $dispatcher);
        $crawler = new Crawler($session, $configuration->getClient(), $configuration->getQueue());

        $promise = $crawler->start($this->factory->getChunk($input));

        $promise->wait();
        $session->finish();
    }
}
