<?php

$name = 'squid';
$colours = 'mixed';
$unit_text = '';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'requests',
        'ds' => 'protoclienthttpreq',
        'colour' => '582a72',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'hits',
        'ds' => 'httphits',
        'colour' => '28774f',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'errs to clnt',
        'ds' => 'httperrors',
        'colour' => '28536c',
    ],
];

require 'includes/html/graphs/generic_v3_multiline.inc.php';
