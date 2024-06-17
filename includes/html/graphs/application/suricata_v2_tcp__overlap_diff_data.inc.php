<?php

$name = 'suricata';
$unit_text = 'events/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 21;

if (isset($vars['sinstance'])) {
    $tcp__overlap_diff_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__overlap_diff_data']);
} else {
    $tcp__overlap_diff_data_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__overlap_diff_data']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__overlap_diff_data_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__overlap_diff_data_rrd_filename,
        'descr' => 'TCP Overlap Diff Data',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__overlap_diff_data_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
