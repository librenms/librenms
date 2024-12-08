<?php

$name = 'suricata';
$unit_text = 'alerts/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $detect__alerts_suppressed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___detect__alerts_suppressed']);
} else {
    $detect__alerts_suppressed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___detect__alerts_suppressed']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($detect__alerts_suppressed_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $detect__alerts_suppressed_rrd_filename,
        'descr' => 'Supressed',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $detect__alerts_suppressed_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
