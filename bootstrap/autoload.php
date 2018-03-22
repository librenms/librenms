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

if (!is_file(__DIR__.'/../vendor/autoload.php')) {
    $message = 'Error: Missing dependencies! Run the following command to fix:';
    $fix = './scripts/composer_wrapper.php install --no-dev';
    if (PHP_SAPI == 'cli') {
        printf("\n%s\n\n%s\n\n", $message, $fix);
    } else {
        printf("<h3 style='color: firebrick;'>%s</h3><p>%s</p>", $message, $fix);
    }
}

require __DIR__.'/../vendor/autoload.php';
