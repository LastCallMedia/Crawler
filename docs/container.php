<?php

namespace {

    use Doctrine\DBAL\DriverManager;
    use LastCall\Crawler\Configuration\Configuration;
    use Psr\Log\NullLogger;

    $container = new Configuration('https://lastcallmedia.com');
    $container['logger'] = function() {
        return new NullLogger();
    };
    $container['doctrine'] = function() {
        return DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
    };


    return $container;
}