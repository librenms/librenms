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
        'ds' => 'toCLOSED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SYN_SENT',
        'ds' => 'toSYN_SENT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'SYN_RECEIVED',
        'ds' => 'toSYN_RECEIVED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'ESTABLISHED',
        'ds' => 'toESTABLISHED',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSE_WAIT',
        'ds' => 'toCLOSE_WAIT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FIN_WAIT_1',
        'ds' => 'toFIN_WAIT_1',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'CLOSING',
        'ds' => 'toCLOSING',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'LAST_ACK',
        'ds' => 'toLAST_ACK',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'FIN_WAIT_2',
        'ds' => 'toFIN_WAIT_2',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'TIME_WAIT',
        'ds' => 'toTIME_WAIT',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'UNKNOWN',
        'ds' => 'toUNKNOWN',
    ],
    [
        'filename' => $rrd_filename,
        'descr' => 'other',
        'ds' => 'toother',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
