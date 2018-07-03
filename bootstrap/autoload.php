<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so we do not have to manually load any of
| our application's PHP classes. It just feels great to relax.
|
*/

$plugin_dir = dirname(__DIR__) . "/html/plugins";

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = include __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('LibreNMS\\Plugins\\', $plugin_dir);

if (!class_exists(\App\Checks::class)) {
    require __DIR__ . '/../app/Checks.php';
}

\App\Checks::postAutoload();
