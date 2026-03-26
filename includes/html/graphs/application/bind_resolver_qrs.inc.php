<?php

$name = 'bind';
$unit_text = 'quiries/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'resolver']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv4 Sent',
        'ds' => 'i4qs',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv6 Sent',
        'ds' => 'i6qs',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv4 Rcvd',
        'ds' => 'i4rr',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv6 Rcvd',
        'ds' => 'i6rr',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
