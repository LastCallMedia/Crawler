<?php


namespace LastCall\Crawler\Common;


use Symfony\Component\Console\Output\OutputInterface;

interface OutputAwareInterface
{
    public function setOutput(OutputInterface $output);
}