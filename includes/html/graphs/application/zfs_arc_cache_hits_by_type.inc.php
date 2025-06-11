<?php

$name = 'zfs';
$unit_text = '% of Hits';
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
        'ds' => 'demand_hits_per',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Prefetch',
        'ds' => 'pre_hits_per',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Demand Meta',
        'ds' => 'meta_hits_per',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Prefetch Meta',
        'ds' => 'pre_meta_hits_per',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
