<?php

$name = 'zfs';
$app_id = $app['app_id'];
$unit_text = 'hits/second';
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
        'ds'       => 'demand_data_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Demand Meta',
        'ds'       => 'demand_meta_hits',
    ];

    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'MRU',
        'ds'       => 'mru_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'MRU Ghost',
        'ds'       => 'mru_ghost_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'MFU',
        'ds'       => 'mfu_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'MFU Ghost',
        'ds'       => 'mfu_ghost_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Prefetch',
        'ds'       => 'pre_data_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Prefetch Meta',
        'ds'       => 'pre_meta_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Anonymous',
        'ds'       => 'anon_hits',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'ARC',
        'ds'       => 'arc_hits',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
