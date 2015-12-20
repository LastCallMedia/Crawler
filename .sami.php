<?php

use Sami\Sami;

return new Sami(__DIR__ . '/src', [
    'title' => 'LCM Crawler',
    'build_dir' => __DIR__ . '/build/docs',
    'cache_dir' => __DIR__ . '/build/cache',
    'remote_repository' => new \Sami\RemoteRepository\GitHubRemoteRepository('LastCallMedia/Crawler', __DIR__)
]);