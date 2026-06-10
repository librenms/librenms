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
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__ssn_from_cache']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__ssn_from_cache']);
}

$rrd_list = [
    'filename' => $rrd_filename,
    'descr' => 'TCP SSN From Cache',
    'ds' => 'data',
];

require 'includes/html/graphs/generic_multi_line.inc.php';
