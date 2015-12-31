<?php

$finder = \Symfony\CS\Finder\DefaultFinder::create()
  ->in(__DIR__.'/src')
  ->in(__DIR__.'/bin')
  ->in(__DIR__.'/docs')
  ->in(__DIR__.'/tests');

return \Symfony\CS\Config\Config::create()
  ->level(\Symfony\CS\FixerInterface::SYMFONY_LEVEL)
  ->fixers(['-psr0'])
  ->setUsingCache(TRUE)
  ->finder($finder);