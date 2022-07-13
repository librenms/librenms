<?php

$name = 'zfs';
$app_id = $app['app_id'];
$unit_text = '% of Hits';
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
        'ds'       => 'demand_hits_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Prefetch',
        'ds'       => 'pre_hits_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Demand Meta',
        'ds'       => 'meta_hits_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Prefetch Meta',
        'ds'       => 'pre_meta_hits_per',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
