<?php

$name = 'suricata';
$unit_text = 'events';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 19;

if (isset($vars['sinstance'])) {
    $tcp__invalid_checksum_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__invalid_checksum']);
} else {
    $tcp__invalid_checksum_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__invalid_checksum']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__invalid_checksum_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__invalid_checksum_rrd_filename,
        'descr' => 'TCP Inv Chksum',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__invalid_checksum_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
