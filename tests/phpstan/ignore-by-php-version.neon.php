<?php

declare(strict_types=1);

use PHPStan\DependencyInjection\NeonAdapter;

$adapter = new NeonAdapter();

$config = [];

if (PHP_VERSION_ID < 80000) {
    //$config = array_merge_recursive($config, $adapter->load(__DIR__ . '/phpstan-php7.neon'));
}

if (PHP_VERSION_ID < 80100) {
    //$config = array_merge_recursive($config, $adapter->load(__DIR__ . '/phpstan-php80.neon'));
}

// If we loaded any extra config
if (count($config) > 0) {
    $config['parameters']['reportUnmatchedIgnoredErrors'] = false;
}

return $config;
