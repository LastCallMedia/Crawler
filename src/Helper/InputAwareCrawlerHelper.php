<?php

namespace LastCall\Crawler\Helper;

use LastCall\Crawler\Configuration\ConfigurationInterface;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Console helper for instantiating crawlers and configurations.
 */
class InputAwareCrawlerHelper extends AbstractCrawlerHelper implements InputAwareInterface
{
    /**
     * @var InputInterface
     */
    private $input;

    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Get the name of the helper.
     *
     * @return string
     */
    public function getName()
    {
        return 'crawler';
    }

    public function getConfiguration()
    {
        if (!$this->input) {
            throw new LogicException('Unable to load configuration - input has not been set.');
        }
        $filename = $this->input->getOption('config');

        return $this->loadConfiguration($filename);
    }

    private function loadConfiguration($filename)
    {
        if (!is_file($filename)) {
            throw new InvalidOptionException(sprintf('Configuration file %s does not exist',
                $filename));
        }
        $configuration = require $filename;
        if ($configuration === 1) {
            throw new RuntimeException(sprintf('Configuration was not returned.'));
        } elseif (!$configuration instanceof ConfigurationInterface) {
            throw new RuntimeException(sprintf('Configuration must implement %s',
                ConfigurationInterface::class));
        }

        return $configuration;
    }
}
