<?php

$name = 'postfix';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Bytes';
$unitlen = 5;
$bigdescrlen = 7;
$smalldescrlen = 7;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Received',
        'ds' => 'bytesr',
        'colour' => '582A72',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Delivered',
        'ds' => 'bytesd',
        'colour' => '88CC88',
    ],
];

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
