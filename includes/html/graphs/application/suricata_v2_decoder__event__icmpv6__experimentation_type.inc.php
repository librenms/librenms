<?php

$name = 'suricata';
$unit_text = 'ICMPv6 pkts/s';
$descr = 'Exp Type';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv6__experimentation_type']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv6__experimentation_type']);
}

require 'includes/html/graphs/generic_stats.inc.php';
