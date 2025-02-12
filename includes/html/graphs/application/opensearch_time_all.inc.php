<?php

$name = 'opensearch';
$unit_text = 'Millisecs/Sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$ti_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'ti']);
$ts_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'ts']);
$tr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tr']);
$tf_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tf']);
$tg_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tg']);
$tm_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tm']);
$tw_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'tw']);

$rrd_list[] = [
    'filename' => $ti_rrd_filename,
    'descr' => 'Index',
    'ds' => 'ti_time',
];
$rrd_list[] = [
    'filename' => $ti_rrd_filename,
    'descr' => 'Delete',
    'ds' => 'ti_del_time',
];
$rrd_list[] = [
    'filename' => $ts_rrd_filename,
    'descr' => 'Query',
    'ds' => 'ts_q_time',
];
$rrd_list[] = [
    'filename' => $ts_rrd_filename,
    'descr' => 'Fetch',
    'ds' => 'ts_f_time',
];
$rrd_list[] = [
    'filename' => $ts_rrd_filename,
    'descr' => 'Scroll',
    'ds' => 'ts_sc_time',
];
$rrd_list[] = [
    'filename' => $ts_rrd_filename,
    'descr' => 'Suggest',
    'ds' => 'ts_su_time',
];
$rrd_list[] = [
    'filename' => $tr_rrd_filename,
    'descr' => 'Refresh',
    'ds' => 'tr_time',
];
$rrd_list[] = [
    'filename' => $tr_rrd_filename,
    'descr' => 'Ext. Refresh',
    'ds' => 'tr_ext_time',
];
$rrd_list[] = [
    'filename' => $tf_rrd_filename,
    'descr' => 'Flush',
    'ds' => 'tf_time',
];
$rrd_list[] = [
    'filename' => $tg_rrd_filename,
    'descr' => 'Get',
    'ds' => 'tg_time',
];
$rrd_list[] = [
    'filename' => $tg_rrd_filename,
    'descr' => 'Get Exists',
    'ds' => 'tg_exists_time',
];
$rrd_list[] = [
    'filename' => $tg_rrd_filename,
    'descr' => 'Get Missing',
    'ds' => 'tg_missing_time',
];
$rrd_list[] = [
    'filename' => $tm_rrd_filename,
    'descr' => 'Merges',
    'ds' => 'tm_time',
];
$rrd_list[] = [
    'filename' => $tw_rrd_filename,
    'descr' => 'Warmer',
    'ds' => 'tw_time',
];

require 'includes/html/graphs/generic_multi_line.inc.php';
