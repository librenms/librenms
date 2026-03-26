<?php

$name = 'bind';
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'server']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Successful',
        'ds' => 'qrisa',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Authoritative',
        'ds' => 'qriaa',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Non Authoritative',
        'ds' => 'qrinaa',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'NX RR Set',
        'ds' => 'qrin',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SERVFAIL',
        'ds' => 'qris',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'NXDOMAIN',
        'ds' => 'qrind',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'With EDNS(0)',
        'ds' => 'rwes',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'Caused Rec',
        'ds' => 'qcr',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
