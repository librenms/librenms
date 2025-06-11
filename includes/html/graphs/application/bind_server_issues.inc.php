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
        'descr' => 'Auth Qry Rej',
        'ds' => 'aqr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Rec Qry Rej',
        'ds' => 'rqr',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Trnctd Rsp Snt',
        'ds' => 'trs',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Oth Qry Fail',
        'ds' => 'oqf',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Qry Dropped',
        'ds' => 'qd',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
