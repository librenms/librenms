<?php

$name = 'nextcloud';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$free_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'free']);
$used_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'used']);
$total_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'total']);

$rrd_list = [];
if (Rrd::checkRrdExists($free_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $free_rrd_filename,
        'descr' => 'free',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $used_rrd_filename,
        'descr' => 'used',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $total_rrd_filename,
        'descr' => 'total',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
