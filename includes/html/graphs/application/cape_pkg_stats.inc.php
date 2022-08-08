<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Count';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app['app_id'],'pkg',$vars['package']]);

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'dropped_files',
        'ds'       => 'dropped_files',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'running_processes',
        'ds'       => 'running_processes',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'api_calls',
        'ds'       => 'api_calls',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'domains',
        'ds'       => 'domains',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'signatures_total',
        'ds'       => 'signatures_total',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'signatures_alert',
        'ds'       => 'signatures_alert',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'files_written',
        'ds'       => 'files_written',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'reg_keys_mod',
        'ds'       => 'reg_keys_mod',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'crash_issues',
        'ds'       => 'crash_issues',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr'    => 'anti_issues',
        'ds'       => 'anti_issues',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
