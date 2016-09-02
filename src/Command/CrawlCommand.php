<?php

namespace LastCall\Crawler\Command;

use LastCall\Crawler\Common\OutputAwareInterface;
use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\Configuration\Factory\ConfigurationFactoryInterface;
use LastCall\Crawler\Session\Session;
use LastCall\Crawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CrawlCommand extends Command
{
    /**
     * @var ConfigurationFactoryInterface
     */
    private $factory;

    public function __construct(ConfigurationFactoryInterface $factory)
    {
        $this->factory = $factory;
        parent::__construct(null);
    }

    public function configure()
    {
        $this->setName($this->factory->getName());
        $this->setDescription($this->factory->getDescription());
        $this->setHelp($this->factory->getHelp());
        $this->factory->configureInput($this->getDefinition());
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->factory->getConfiguration($input);

        if ($configuration instanceof OutputAwareInterface) {
            $configuration->setOutput($output);
        }

        $promise = $this->getCrawler($configuration)
            ->start($this->factory->getChunk($input));

        $promise->wait();
    }

    protected function getCrawler(ConfigurationInterface $configuration)
    {
        $dispatcher = new EventDispatcher();
        $session = Session::createFromConfig($configuration, $dispatcher);

        return new Crawler($session, $configuration->getClient(), $configuration->getQueue());
    }
}
