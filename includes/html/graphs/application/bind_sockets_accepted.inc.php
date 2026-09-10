<?php

$name = 'bind';
$unit_text = 'accepted/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'sockets']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv4',
        'ds' => 'ti4ca',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv6',
        'ds' => 'ti6ca',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
