<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Count';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$packages = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'cape', 'pkg___-___');

$rrd_list = [];
$packages_int = 0;
foreach ($packages as $index => $package) {
    $label = preg_filter('/^pkg\_\_\_\-\_\_\_\-/', '', $package);
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $package]);
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $label,
        'ds' => 'tasks',
    ];

    $packages_int++;
}

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg___-___', $vars['package']]);

if (sizeof($rrd_list)) {
    d_echo('No relevant package RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
