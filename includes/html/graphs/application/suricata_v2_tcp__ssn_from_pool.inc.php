<?php

$name = 'suricata';
$unit_text = 'events/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 15;
$descr_len = 17;

if (isset($vars['sinstance'])) {
    $tcp__ssn_from_pool_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__ssn_from_pool']);
} else {
    $tcp__ssn_from_pool_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__ssn_from_pool']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($tcp__ssn_from_pool_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $tcp__ssn_from_pool_rrd_filename,
        'descr' => 'TCP SSN From Pool',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $tcp__ssn_from_pool_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
