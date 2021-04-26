<?php

$name = 'zfs';
$app_id = $app['app_id'];
$unit_text = 'per second';
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
        'descr'    => 'Deleted',
        'ds'       => 'deleted',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Recycle Misses',
        'ds'       => 'recycle_miss',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Evict Skip',
        'ds'       => 'evict_skip',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Mutex Skip',
        'ds'       => 'mutex_skip',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
