<?php

$name = 'zfs';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Frequently Used',
        'ds' => 'mfu_size',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Recently Used',
        'ds' => 'p',
    ]
];

require 'includes/html/graphs/generic_multi_line.inc.php';
