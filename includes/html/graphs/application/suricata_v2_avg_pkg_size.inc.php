<?php

$name = 'suricata';
$unit_text = 'btyes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $decoder__avg_pkt_size_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__avg_pkt_size']);
} else {
    $decoder__avg_pkt_size_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__avg_pkt_size']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($decoder__avg_pkt_size_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__avg_pkt_size_rrd_filename,
        'descr' => 'Avg Pkt Size',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $decoder__bytes_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
