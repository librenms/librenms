<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'API Calls Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['stddev'])) {
    if ($vars['stddev'] != 'on' and $vars['stddev'] != 'off') {
        $vars['stddev'] = 'off';
    }
} else {
    $vars['stddev'] = 'off';
}

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-api_calls___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'api_calls']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Min',
        'ds'       => 's0api_calls',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Max',
        'ds'       => 's1api_calls',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Mean',
        'ds'       => 's3api_calls',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Median',
        'ds'       => 's4api_calls',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Mode',
        'ds'       => 's5api_calls',
    ];
    if ($vars['stddev'] == 'on') {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => 'StdDev',
            'ds'       => 's7api_calls',
        ];
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => 'StdDevP',
            'ds'       => 's9api_calls',
        ];
    }
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
