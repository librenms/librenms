#!/usr/bin/env php
<?php

$init_modules = [];
require __DIR__ . '/includes/init.php';

$options = getopt('drfpgh:');

c_echo("%YWarning: check-services.php is deprecated, use lnms services:poll or enable the services polling module instead%n\n");

Artisan::call('services:poll',
    ['device spec' => $options['h'] ?? null, '--verbose' => isset($options['d']) ? 3 : 1],
    new \Symfony\Component\Console\Output\ConsoleOutput
);
