<?php

$name = 'opensearch';
$app_id = $app['app_id'];
$unit_text = 'Millisecs/Sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Index',
        'ds'       => 'ti_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Delete',
        'ds'       => 'ti_del_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Query',
        'ds'       => 'ts_q_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Fetch',
        'ds'       => 'ts_f_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Scroll',
        'ds'       => 'ts_sc_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Suggest',
        'ds'       => 'ts_su_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Refresh',
        'ds'       => 'tr_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Ext. Refresh',
        'ds'       => 'tr_ext_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Flush',
        'ds'       => 'tf_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Get',
        'ds'       => 'tg_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Get Exists',
        'ds'       => 'tg_exists_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Get Missing',
        'ds'       => 'tg_missing_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Merges',
        'ds'       => 'tm_time',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'Warmer',
        'ds'       => 'tw_time',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
