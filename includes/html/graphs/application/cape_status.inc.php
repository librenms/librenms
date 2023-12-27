<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Run Count';
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
        'descr' => 'banned',
        'ds' => 'banned',
    ];
//    $rrd_list[] = [
//        'filename' => $rrd_filename,
//        'descr'    => 'pending',
//        'ds'       => 'pending',
//    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'running',
        'ds' => 'running',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'completed',
        'ds' => 'completed',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'distributed',
        'ds' => 'distributed',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'reported',
        'ds' => 'reported',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'recovered',
        'ds' => 'recovered',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'failed_analysis',
        'ds' => 'failed_analysis',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'failed_processing',
        'ds' => 'failed_processing',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'failed_reporting',
        'ds' => 'failed_reporting',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
