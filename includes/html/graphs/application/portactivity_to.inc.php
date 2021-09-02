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
        'ds'       => 'toCLOSED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SYN_SENT',
        'ds'       => 'toSYN_SENT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SYN_RECEIVED',
        'ds'       => 'toSYN_RECEIVED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'ESTABLISHED',
        'ds'       => 'toESTABLISHED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSE_WAIT',
        'ds'       => 'toCLOSE_WAIT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FIN_WAIT_1',
        'ds'       => 'toFIN_WAIT_1',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSING',
        'ds'       => 'toCLOSING',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'LAST_ACK',
        'ds'       => 'toLAST_ACK',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FIN_WAIT_2',
        'ds'       => 'toFIN_WAIT_2',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TIME_WAIT',
        'ds'       => 'toTIME_WAIT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UNKNOWN',
        'ds'       => 'toUNKNOWN',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'other',
        'ds'       => 'toother',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
