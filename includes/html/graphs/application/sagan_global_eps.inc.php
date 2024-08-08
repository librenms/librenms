<?php

$name = 'sagan';
$unit_text = 'Events Per Second';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_list = [];

foreach (\App\Models\Application::query()->where('app_type', 'sagan')->lazy() as $app) {
    $device = \App\Models\Device::query()->where('device_id', $app->device_id)->first();

    $rrd_filename = Rrd::name($device->hostname, ['app', $name, $app->app_id]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $device->hostname,
            'ds' => 'eps',
        ];
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
