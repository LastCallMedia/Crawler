<?php

use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Uri\Normalizations;
use LastCall\Crawler\Uri\Normalizer;
use Symfony\Component\Console\Logger\ConsoleLogger;

include_once __DIR__.'/../vendor/autoload.php';

// Create a new configuration, using our website as a base URL.
$config = new Configuration('http://localhost/lcmscaffold/docroot/', [
    // Add a logger.  Normally, we'd use something like Monolog.
    // In this case, we'll just log directly to the console.
    'logger' => function () use (&$config) {
        return new ConsoleLogger($config['output']);
    },
    // Specify which log listeners are active.
    // @see LastCall\Crawler\Configuration\ServiceProvider\LoggerServiceProvider
    'loggers' => [
        'request',
        'exception',
    ],
    // Specify which discoverers are active.
    // @see LastCall\Crawler\Configuration\ServiceProvider\RecursionServiceProvider
    'discoverers' => [
        'link',
        'redirect',
        // 'image',
        // 'script',
        // 'stylesheet',
    ],
    // Specify which recursors are active.
    // @see LastCall\Crawler\Configuration\ServiceProvider\RecursionServiceProvider
    'recursors' => [
        'internal_html',
        // 'internal_asset',
    ],
    // Use a custom normalizer to transform URLs to a consistent state.
    'normalizer' => new Normalizer([
        Normalizations::decodeUnreserved(),
        Normalizations::capitalizeEscaped(),
        Normalizations::dropFragment(),
    ]),
]);

// Return the Configuration so the CLI runner can run it.
return $config;
