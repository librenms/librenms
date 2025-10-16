<?php

$name = 'bind';
$unit_text = 'Table Size';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'adb']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Address',
        'ds' => 'ahts',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Name',
        'ds' => 'nhts',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
