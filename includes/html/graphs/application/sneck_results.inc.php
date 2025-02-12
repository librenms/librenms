<?php

$name = 'sneck';
$unit_text = 'Results';
$colours = 'psychedelic';
$dostack = 0;
$descr_len = 10;
$addarea = 0;
$transparency = 15;
$float_precision = 0;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'OK',
        'ds' => 'ok',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Warning',
        'ds' => 'warning',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Critical',
        'ds' => 'critical',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Unknown',
        'ds' => 'unknown',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Errored',
        'ds' => 'errored',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
