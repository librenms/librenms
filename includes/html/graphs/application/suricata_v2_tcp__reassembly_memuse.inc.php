<?php

$name = 'suricata';
$unit_text = 'bytes';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 18;

if (isset($vars['sinstance'])) {
    $tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__reassembly_memuse']);
} else {
    $tcp__reassembly_memuse_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__reassembly_memuse']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__reassembly_memuse_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__reassembly_memuse_rrd_filename,
        'descr' => 'TCP Reass Memuse',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__reassembly_memuse_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
