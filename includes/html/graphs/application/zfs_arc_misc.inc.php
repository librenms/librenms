<?php

$name = 'zfs';
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Deleted',
        'ds' => 'deleted',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Recycle Misses',
        'ds' => 'recycle_miss',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Evict Skip',
        'ds' => 'evict_skip',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Mutex Skip',
        'ds' => 'mutex_skip',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
