#!/usr/bin/env php
<?php

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;
use Symfony\Component\Process\Process;

$ping_start = microtime(true);

$init_modules = ['alerts', 'eloquent'];
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

if ($config['noinfluxdb'] !== true && $config['influxdb']['enable'] === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

rrdtool_initialize();

$pinger = new \LibreNMS\Pinger($groups);
$pinger->start();

rrdtool_close();

printf("Pinged %s devices in %.2fs\n", $pinger->count(), microtime(true) - $ping_start);
