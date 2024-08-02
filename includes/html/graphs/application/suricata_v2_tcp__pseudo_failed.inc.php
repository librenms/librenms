<?php

$name = 'suricata';
$unit_text = 'events/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 18;

if (isset($vars['sinstance'])) {
    $tcp__pseudo_failed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__pseudo_failed']);
} else {
    $tcp__pseudo_failed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__pseudo_failed']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__pseudo_failed_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__pseudo_failed_rrd_filename,
        'descr' => 'TCP Pseudo Failed',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__pseudo_failed_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
