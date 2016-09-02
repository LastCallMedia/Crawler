<?php

namespace LastCall\Crawler\Configuration\Factory;

use LastCall\Crawler\Configuration\ConfigurationInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class PreloadedConfigurationFactory implements ConfigurationFactoryInterface
{
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getName()
    {
        return 'crawler';
    }

    public function getDescription()
    {
        return 'Execute a pre-loaded configuration.';
    }

    public function getHelp()
    {
        return 'No arguments required.';
    }

    public function configureInput(InputDefinition $definition)
    {
        // no-op
    }

    public function getChunk(InputInterface $input)
    {
        return 5;
    }

    public function getConfiguration(InputInterface $input)
    {
        return $this->configuration;
    }
}
