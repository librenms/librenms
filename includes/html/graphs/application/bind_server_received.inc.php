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
        'descr' => 'IPv4 Req',
        'ds' => 'i4rr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'IPv6 Req',
        'ds' => 'i6rr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'TCP Req',
        'ds' => 'trr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'TCP Qry',
        'ds' => 'tqr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'UDP Qry',
        'ds' => 'uqr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'With EDNS(0)',
        'ds' => 'rwer',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Other EDNS',
        'ds' => 'oeor',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Dup. Qry',
        'ds' => 'dqr',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
