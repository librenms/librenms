<?php

$name = 'zfs';
$app_id = $app['app_id'];
$unit_text = 'misses/second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Demand',
        'ds'       => 'demand_data_misses',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Demand Meta',
        'ds'       => 'demand_meta_misses',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Prefetch',
        'ds'       => 'pre_data_misses',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Prefetch Meta',
        'ds'       => 'pre_meta_misses',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'ARC',
        'ds'       => 'arc_misses',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
