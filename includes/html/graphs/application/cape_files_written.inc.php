<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Files Written Per Run';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

$vars['stddev'] = ($vars['stddev'] ?? 'off') === 'on' ? 'on' : 'off';

if (isset($vars['package'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-files_written___-___', $vars['package']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'files_written']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Min',
        'ds' => 's0files_written',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Max',
        'ds' => 's1files_written',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Mean',
        'ds' => 's3files_written',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Median',
        'ds' => 's4files_written',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Mode',
        'ds' => 's5files_written',
    ],
];

if (isset($vars['stdset']) && $vars['stdset'] == 'on') {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'StdDev',
        'ds' => 's7files_written',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'StdDevP',
        'ds' => 's9files_written',
    ];
}

require 'includes/html/graphs/generic_multi_line.inc.php';
