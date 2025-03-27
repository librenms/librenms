<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Lines';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'debug',
        'ds' => 'debug',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'info',
        'ds' => 'info',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'warning',
        'ds' => 'warning',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'error',
        'ds' => 'error',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'critical',
        'ds' => 'critical',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
