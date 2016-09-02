<?php

namespace LastCall\Crawler\Configuration\Factory;

use LastCall\Crawler\Configuration\ConfigurationInterface;
use LastCall\Crawler\CrawlerEvents;
use LastCall\Crawler\Event\CrawlerStartEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class PHPFileConfigurationFactory implements ConfigurationFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'crawl';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Execute a crawler session on a configuration.';
    }

    /**
     * {@inheritdoc}
     */
    public function getHelp()
    {
        return 'Pass in the name of a PHP file that contains the crawler configuration.';
    }

    public function configureInput(InputDefinition $definition)
    {
        $definition->addArgument(new InputArgument('filename', InputArgument::OPTIONAL, 'Path to a configuration file.', 'crawler.php'));
        $definition->addOption(new InputOption('chunk', 'c', InputOption::VALUE_REQUIRED, 'The amount of items to process.', 5));
    }

    /**
     * {@inheritdoc}
     */
    public function getChunk(InputInterface $input)
    {
        return $input->getOption('chunk');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(InputInterface $input)
    {
        $filename = $input->getArgument('filename');

        if (!file_exists($filename)) {
            throw new \Exception(sprintf('Configuration file %s does not exist.', $filename));
        }
        $configuration = require $filename;

        if (!$configuration || !$configuration instanceof ConfigurationInterface) {
            throw new \Exception(sprintf('Configuration must implement %s', ConfigurationInterface::class));
        }

        if ($input->hasOption('reset')) {
            $configuration->addListener(CrawlerEvents::START, function (CrawlerStartEvent $event) use ($configuration) {
                $event->getSession()->teardown();
                $event->getSession()->setup();
            });
        }

        return $configuration;
    }
}
