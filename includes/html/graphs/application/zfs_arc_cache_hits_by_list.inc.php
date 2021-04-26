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
        'descr'    => 'Anon Used',
        'ds'       => 'anon_hits_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Most Recent',
        'ds'       => 'mru_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Most Frequent',
        'ds'       => 'mfu_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'MRU Ghost',
        'ds'       => 'mru_ghost_per',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'MFU Ghost',
        'ds'       => 'mfu_ghost_per',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
