<?php

$name = 'bind';
$unit_text = 'errors / sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app->app_id, 'sockets']);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv4 send',
        'ds' => 'ui4se',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv6 send',
        'ds' => 'ui6se',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv4 send',
        'ds' => 'ti4se',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv6 send',
        'ds' => 'ti6se',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv4 recv',
        'ds' => 'ui4re',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UDP/IPv6 recv',
        'ds' => 'ui6re',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv4 recv',
        'ds' => 'ti4re',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TCP/IPv6 recv',
        'ds' => 'ti6re',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
