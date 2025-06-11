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
        'descr' => 'ARC Size',
        'ds' => 'arc_size',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Target Size',
        'ds' => 'target_size',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Target Max',
        'ds' => 'target_size_max',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Target Min',
        'ds' => 'target_size_min',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
