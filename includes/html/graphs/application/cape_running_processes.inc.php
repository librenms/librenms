<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Running Procs Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-running_processes___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'running_processes']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Min',
        'ds' => 's0running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Max',
        'ds' => 's1running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Mean',
        'ds' => 's3running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Median',
        'ds' => 's4running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Mode',
        'ds' => 's5running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'StdDev',
        'ds' => 's7running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'StdDevP',
        'ds' => 's9running_processes',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
