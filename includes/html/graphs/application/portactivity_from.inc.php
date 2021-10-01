<?php

$name = 'portactivity';
$app_id = $app['app_id'];
$unit_text = 'Connections';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], $vars['port']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSED',
        'ds'       => 'fromCLOSED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SYN_SENT',
        'ds'       => 'fromSYN_SENT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SYN_RECEIVED',
        'ds'       => 'fromSYN_RECEIVED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'ESTABLISHED',
        'ds'       => 'fromESTABLISHED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSE_WAIT',
        'ds'       => 'fromCLOSE_WAIT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FIN_WAIT_1',
        'ds'       => 'fromFIN_WAIT_1',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSING',
        'ds'       => 'fromCLOSING',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'LAST_ACK',
        'ds'       => 'fromLAST_ACK',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FIN_WAIT_2',
        'ds'       => 'fromFIN_WAIT_2',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TIME_WAIT',
        'ds'       => 'fromTIME_WAIT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UNKNOWN',
        'ds'       => 'fromUNKNOWN',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'other',
        'ds'       => 'fromother',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
