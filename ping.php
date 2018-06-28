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

$options = getopt('dv');
set_debug(isset($options['d']));

if (isset($options['v'])) {
    global $vdebug;
    $vdebug = true;
}

if ($config['noinfluxdb'] !== true && $config['influxdb']['enable'] === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

rrdtool_initialize();

$pinger = new \LibreNMS\Pinger();
$pinger->start();

rrdtool_close();

printf("Pinged %s devices in %.2fs\n", $pinger->count(), microtime(true) - $ping_start);
