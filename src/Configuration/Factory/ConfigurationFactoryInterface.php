<?php


namespace LastCall\Crawler\Configuration\Factory;


use LastCall\Crawler\Configuration\ConfigurationInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

interface ConfigurationFactoryInterface {

  public function getName();

  public function getDescription();

  public function getHelp();

  /**
   * @param \Symfony\Component\Console\Input\InputDefinition $definition
   */
  public function configureInput(InputDefinition $definition);

  /**
   * @return ConfigurationInterface
   */
  public function getConfiguration(InputInterface $input);

  /**
   * @return int
   */
  public function getChunk(InputInterface $input);
}