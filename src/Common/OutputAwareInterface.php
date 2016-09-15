<?php

namespace LastCall\Crawler\Common;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines an object that can send console output.
 */
interface OutputAwareInterface
{
    /**
     * Attach console output.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput(OutputInterface $output);
}
