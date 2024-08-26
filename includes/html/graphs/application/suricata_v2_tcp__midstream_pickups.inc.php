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
    $tcp__midstream_pickups_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__midstream_pickups']);
} else {
    $tcp__midstream_pickups_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__midstream_pickups']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__midstream_pickups_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__midstream_pickups_rrd_filename,
        'descr' => 'TCP Midstream Pickups',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__midstream_pickups_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
