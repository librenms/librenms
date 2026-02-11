<?php

$name = 'portactivity';
$unit_text = 'Connections';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['port']]);

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSED',
        'ds' => 'totalCLOSED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SYN_SENT',
        'ds' => 'totalSYN_SENT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SYN_RECEIVED',
        'ds' => 'totalSYN_RECEIVED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'ESTABLISHED',
        'ds' => 'totalESTABLISHED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSE_WAIT',
        'ds' => 'totalCLOSE_WAIT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FIN_WAIT_1',
        'ds' => 'totalFIN_WAIT_1',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSING',
        'ds' => 'totalCLOSING',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'LAST_ACK',
        'ds' => 'totalLAST_ACK',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FIN_WAIT_2',
        'ds' => 'totalFIN_WAIT_2',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TIME_WAIT',
        'ds' => 'totalTIME_WAIT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UNKNOWN',
        'ds' => 'totalUNKNOWN',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'other',
        'ds' => 'totalother',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
