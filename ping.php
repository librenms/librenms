#!/usr/bin/env php
<?php

use App\Jobs\PingCheck;

$init_modules = ['alerts', 'laravel', 'nodb'];
require __DIR__ . '/includes/init.php';

$options = getopt('hdvg:');

if (isset($options['h'])) {
    echo <<<'END'
ping.php: Usage ping.php [-d] [-v] [-g group(s)]
  -d enable debug output
  -v enable verbose debug output
  -g only ping devices for this poller group, may be comma separated list

END;
    exit;
}

set_debug(isset($options['d']));

if (isset($options['v'])) {
    global $vdebug;
    $vdebug = true;
}

if (isset($options['g'])) {
    $groups = explode(',', $options['g']);
} else {
    $groups = [];
}

if (Config::get('base_url') !== true && \LibreNMS\Config::get('influxdb.enable') === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

rrdtool_initialize();

PingCheck::dispatch($groups);

rrdtool_close();
