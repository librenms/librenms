<?php

$name = 'suricata';
$unit_text = 'ICMPv6 pkts/s';
$descr = 'Unass Type';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv6__unassigned_type']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv6__unassigned_type']);
}

require 'includes/html/graphs/generic_stats.inc.php';
