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
        'ds'       => 'totalCLOSED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SYN_SENT',
        'ds'       => 'totalSYN_SENT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'SYN_RECEIVED',
        'ds'       => 'totalSYN_RECEIVED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'ESTABLISHED',
        'ds'       => 'totalESTABLISHED',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSE_WAIT',
        'ds'       => 'totalCLOSE_WAIT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FIN_WAIT_1',
        'ds'       => 'totalFIN_WAIT_1',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'CLOSING',
        'ds'       => 'totalCLOSING',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'LAST_ACK',
        'ds'       => 'totalLAST_ACK',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'FIN_WAIT_2',
        'ds'       => 'totalFIN_WAIT_2',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'TIME_WAIT',
        'ds'       => 'totalTIME_WAIT',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'UNKNOWN',
        'ds'       => 'totalUNKNOWN',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'other',
        'ds'       => 'totalother',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
