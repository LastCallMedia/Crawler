<?php

namespace LastCall\Crawler\Configuration;


use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigUtil
{
    static function loggerFactoryFn($dir, OutputInterface $output = NULL) {
        return function($name, $use_console = TRUE) use ($dir, $output) {
            $handlers = array();
            $handlers[] = new StreamHandler(sprintf('%s/%s.log', $dir, $name));
            if($use_console && $output) {
                $handlers[] = new \Symfony\Bridge\Monolog\Handler\ConsoleHandler($output);
            }
            return new \Monolog\Logger($name, $handlers);
        };
    }

    static function clearLogDirFn($dir) {
        return function() use ($dir) {
            (new Filesystem())->remove(glob($dir . '/*.log'));
        };
    }

}