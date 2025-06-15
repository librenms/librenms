<?php

$name = 'zfs';
$unit_text = 'misses/second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Demand',
        'ds' => 'demand_data_misses',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Demand Meta',
        'ds' => 'demand_meta_misses',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Prefetch',
        'ds' => 'pre_data_misses',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Prefetch Meta',
        'ds' => 'pre_meta_misses',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'ARC',
        'ds' => 'arc_misses',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
