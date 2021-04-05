<?php

$name = 'bind';
$app_id = $app['app_id'];
$unit_text = 'per second';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'bind', $app['app_id'], 'server']);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Auth Qry Rej',
        'ds'       => 'aqr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Rec Qry Rej',
        'ds'       => 'rqr',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Trnctd Rsp Snt',
        'ds'       => 'trs',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Oth Qry Fail',
        'ds'       => 'oqf',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Qry Dropped',
        'ds'       => 'qd',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
