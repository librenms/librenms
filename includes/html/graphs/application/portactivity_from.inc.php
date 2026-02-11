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
        'ds' => 'fromCLOSED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SYN_SENT',
        'ds' => 'fromSYN_SENT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SYN_RECEIVED',
        'ds' => 'fromSYN_RECEIVED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'ESTABLISHED',
        'ds' => 'fromESTABLISHED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSE_WAIT',
        'ds' => 'fromCLOSE_WAIT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FIN_WAIT_1',
        'ds' => 'fromFIN_WAIT_1',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSING',
        'ds' => 'fromCLOSING',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'LAST_ACK',
        'ds' => 'fromLAST_ACK',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FIN_WAIT_2',
        'ds' => 'fromFIN_WAIT_2',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TIME_WAIT',
        'ds' => 'fromTIME_WAIT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UNKNOWN',
        'ds' => 'fromUNKNOWN',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'other',
        'ds' => 'fromother',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
