<?php

$unitlen = 10;
$bigdescrlen = 9;
$smalldescrlen = 9;
$dostack = 0;
$printtotal = 0;
$unit_text = 'Events';
$colours = 'psychedelic';
$rrd_list = [];

$rrd_filename = Rrd::name($device['hostname'], ['app', 'puppet-agent', $app->app_id, 'events']);
$array = [
    'success',
    'failure',
    'total',
];
$no_data_text = "No Data file $rrd_filename";

foreach ($array as $ds) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $ds,
        'ds' => $ds,
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
