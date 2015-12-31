<?php

use LastCall\Crawler\Configuration\Configuration;
use LastCall\Crawler\Uri\Normalizations;
use Symfony\Component\Console\Logger\ConsoleLogger;

include_once __DIR__.'/../vendor/autoload.php';

// Create a new configuration, using our website as a base URL.
$config = new Configuration('https://lastcallmedia.com');

// Add some normalizers to clean up URLs.
$config->extend('normalizers', function (array $normalizers) {
    $normalizers['convert_to_ssl'] = Normalizations::rewriteScheme(['http' => 'https']);

    return $normalizers;
});

// Add a logger.  Normally, we'd use something like Monolog.
// In this case, we'll just log directly to the console.
$config['logger'] = function () use ($config) {
    return new ConsoleLogger($config['output']);
};

// Add an event subscriber.
$config->extend('subscribers', function($subscribers) {
    // Add your subscriber here.  Use a descriptive array key
    // so you can find it later if you need to.
    //$subscribers['mysubscriber'] = new MySubscriber();

    return $subscribers;
});

// Return the Configuration so the CLI runner can run it.
return $config;
