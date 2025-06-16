<?php

$name = 'bind';
$unit_text = 'active sockets';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'sockets']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv4',
        'ds' => 'ui4sa',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv6',
        'ds' => 'ui6sa',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv6',
        'ds' => 'ti6sa',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Raw',
        'ds' => 'rsa',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
