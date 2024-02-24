<?php

$name = 'logsize';
$app_id = $app['app_id'];
$unit_text = 'Bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 0;
$float_precision = 3;

require 'logsize-common.inc.php';

$rrd_list = [];
if (Rrd::checkRrdExists($filename)) {
    $rrd_list[] = [
        'filename' => $filename,
        'descr' => 'Max Size',
        'ds' => 'max_size',
    ];
    $rrd_list[] = [
        'filename' => $filename,
        'descr' => 'Mean Size',
        'ds' => 'mean_size',
    ];
    $rrd_list[] = [
        'filename' => $filename,
        'descr' => 'Median Size',
        'ds' => 'median_size',
    ];
    $rrd_list[] = [
        'filename' => $filename,
        'descr' => 'Mode Size',
        'ds' => 'mode_size',
    ];
    $rrd_list[] = [
        'filename' => $filename,
        'descr' => 'Min Size',
        'ds' => 'min_size',
    ];
} else {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
