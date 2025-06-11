<?php

$name = 'zfs';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['pool']]);

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'Size',
        'ds' => 'size',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Allocated',
        'ds' => 'alloc',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Free',
        'ds' => 'free',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Expand Size',
        'ds' => 'expandsz',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
