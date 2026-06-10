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
        'descr' => 'IPv4 NS Ftchd',
        'ds' => 'i4naf',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv6 NS Ftchd',
        'ds' => 'i6naf',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv4 Failed',
        'ds' => 'i4naff',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'IPv6 Failed',
        'ds' => 'i6naff',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
