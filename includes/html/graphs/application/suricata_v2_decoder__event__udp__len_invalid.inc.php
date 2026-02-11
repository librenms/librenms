<?php

$name = 'suricata';
$unit_text = 'UDP pkts/s';
$descr = 'Len Invalid';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__udp__len_invalid']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__udp__len_invalid']);
}

require 'includes/html/graphs/generic_stats.inc.php';
