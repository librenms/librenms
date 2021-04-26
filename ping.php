#!/usr/bin/env php
<?php

use App\Jobs\PingCheck;
use LibreNMS\Data\Store\Datastore;

$init_modules = ['alerts', 'laravel', 'nodb'];
require __DIR__ . '/includes/init.php';

$options = getopt('hdvrg:');

if (isset($options['h'])) {
    echo <<<'END'
ping.php: Usage ping.php [-d] [-v] [-r] [-g group(s)]
  -d enable debug output
  -v enable verbose debug output
  -r do not create or update RRDs
  -g only ping devices for this poller group, may be comma separated list

END;
    exit;
}

set_debug(isset($options['d']));

if (isset($options['v'])) {
    global $vdebug;
    $vdebug = true;
}

Datastore::init($options);

if (isset($options['g'])) {
    $groups = explode(',', $options['g']);
} else {
    $groups = [];
}

PingCheck::dispatch($groups);
