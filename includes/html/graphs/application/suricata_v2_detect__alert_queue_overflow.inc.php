<?php

$name = 'suricata';
$unit_text = 'overflow/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $detect__alert_queue_overflow_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___detect__alert_queue_overflow']);
} else {
    $detect__alert_queue_overflow_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___detect__alert_queue_overflow']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($detect__alert_queue_overflow_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $detect__alert_queue_overflow_rrd_filename,
        'descr' => 'Alert Queue',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $detect__alert_queue_overflow_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
