<?php

namespace LastCall\Crawler\Configuration;

use Symfony\Component\Console\Input\InputInterface;

interface InputAwareConfigurationInterface
{
    /**
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    public static function getInputDefinition();

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return self
     */
    public static function createFromInput(InputInterface $input);
}
