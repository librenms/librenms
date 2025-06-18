<?php

$name = 'privoxy';
$unit_text = 'Reponse Types';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => '1xx',
        'ds' => 'resp_1xx',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '2xx',
        'ds' => 'resp_2xx',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '3xx',
        'ds' => 'resp_3xx',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '4xx',
        'ds' => 'resp_4xx',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => '5xx',
        'ds' => 'resp_5xx',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
