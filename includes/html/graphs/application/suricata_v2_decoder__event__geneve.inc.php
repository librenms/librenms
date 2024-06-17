<?php

$name = 'suricata';
$unit_text = 'Geneve packets/s';
$descr = 'Unknown Payload';
$descr_len = 20;
$ds = 'data';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__geneve__unknown_payload_type']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__geneve__unknown_payload_type']);
}

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $decoder__event__dce__pkt_too_small_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
