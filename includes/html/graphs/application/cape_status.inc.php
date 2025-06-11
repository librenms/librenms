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

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'banned',
        'ds' => 'banned',
    ],
    //    $rrd_list[] = [
    //        'filename' => $rrd_filename,
    //        'descr'    => 'pending',
    //        'ds'       => 'pending',
    //    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'running',
        'ds' => 'running',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'completed',
        'ds' => 'completed',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'distributed',
        'ds' => 'distributed',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'reported',
        'ds' => 'reported',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'recovered',
        'ds' => 'recovered',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'failed_analysis',
        'ds' => 'failed_analysis',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'failed_processing',
        'ds' => 'failed_processing',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'failed_reporting',
        'ds' => 'failed_reporting',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
