<?php

$name = 'opensearch';
$unit_text = 'Fetches';
$colours = 'greens';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'c']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'In Fl. Fetches',
        'ds' => 'c_in_fl_fetch',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
