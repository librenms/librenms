<?php

$name = 'zfs';
$unit_text = '% of Max';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'ARC Size%',
        'ds' => 'arc_size_per',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Target Size%',
        'ds' => 'target_size_per',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Target Min%',
        'ds' => 'min_size_per',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
