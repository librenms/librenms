<?php

$name = 'cape';
$app_id = $app['app_id'];
$unit_text = 'Run Count';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$float_precision = 3;

if (isset($vars['package'])) {
    $rrd_filename_anti_issues = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-anti_issues___-___', $vars['package']]);
    $rrd_filename_api_calls = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-api_calls___-___', $vars['package']]);
    $rrd_filename_domains = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-domains___-___', $vars['package']]);
    $rrd_filename_crash_issues = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-crash_issues___-___', $vars['package']]);
    $rrd_filename_dropped_files = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-dropped_files___-___', $vars['package']]);
    $rrd_filename_files_written = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-files_written___-___', $vars['package']]);
    $rrd_filename_reg_keys_mod = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-reg_keys_mod___-___', $vars['package']]);
    $rrd_filename_running_processes = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-running_processes___-___', $vars['package']]);
    $rrd_filename_signatures_alert = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-signatures_alert___-___', $vars['package']]);
    $rrd_filename_signatures_total = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'pkg-signatures_total___-___', $vars['package']]);
} else {
    $rrd_filename_anti_issues = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'anti_issues']);
    $rrd_filename_api_calls = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'api_calls']);
    $rrd_filename_domains = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'domains']);
    $rrd_filename_crash_issues = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'crash_issues']);
    $rrd_filename_dropped_files = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'dropped_files']);
    $rrd_filename_files_written = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'files_written']);
    $rrd_filename_signatures_total = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'signatures_total']);
    $rrd_filename_reg_keys_mod = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'reg_keys_mod']);
    $rrd_filename_running_processes = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'running_processes']);
    $rrd_filename_signatures_alert = Rrd::name($device['hostname'], ['app', $name, $app['app_id'], 'signatures_alert']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename_anti_issues)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_anti_issues,
        'descr' => 'anti_issues',
        'ds' => 'anti_issues',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_api_calls)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_api_calls,
        'descr' => 'api_calls',
        'ds' => 'api_calls',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_domains)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_domains,
        'descr' => 'domains',
        'ds' => 'domains',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_crash_issues)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_crash_issues,
        'descr' => 'crash_issues',
        'ds' => 'crash_issues',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_dropped_files)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_dropped_files,
        'descr' => 'dropped_files',
        'ds' => 'dropped_files',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_files_written)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_files_written,
        'descr' => 'files_written',
        'ds' => 'files_written',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_reg_keys_mod)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_reg_keys_mod,
        'descr' => 'reg_keys_mod',
        'ds' => 'reg_keys_mod',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_running_processes)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_running_processes,
        'descr' => 'running_processes',
        'ds' => 'running_processes',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_signatures_alert)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_signatures_alert,
        'descr' => 'signatures_alert',
        'ds' => 'signatures_alert',
    ];
}
if (Rrd::checkRrdExists($rrd_filename_signatures_total)) {
    $rrd_list[] = [
        'filename' => $rrd_filename_signatures_total,
        'descr' => 'signatures_total',
        'ds' => 'signatures_total',
    ];
}

require 'includes/html/graphs/generic_multi_line.inc.php';
