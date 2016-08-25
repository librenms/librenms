#!/usr/bin/env php
<?php

$failed = false;

if (version_compare(PHP_VERSION, '5.6') >= 0) {
    $lint = `find . -path './vendor' -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l | grep -v '^No syntax errors detected' ; test $? -eq 1`;
} else {
    $lint = `find . -regextype posix-extended -regex "\./(lib/influxdb-php|vendor)" -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l | grep -v '^No syntax errors detected' ; test $? -eq 1`;
}

if (!empty($lint)) {
    echo "lint check has failed\n";
    print_r($lint);
    $failed = true;
}

$phpcs = `./vendor/bin/phpcs -n -p --colors --extensions=php --standard=PSR2 --ignore=html/lib/* --ignore=html/plugins/ html`;

if (!empty($phpcs)) {
    echo "PSR2 check has failed\n";
    print_r($phpcs);
    $failed = true;
}

$phpunit = `./vendor/bin/phpunit`;

if(!strstr($phpunit, "OK")) {
    echo "phpunit tests have failed\n";
    print_r($phpunit);
    $failed = true;
}

if ($failed === false) {
    echo "Tests ok, submit away :)\n";
}
