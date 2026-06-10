<?php

$name = 'suricata';
$unit_text = 'IPv6 pkts/s';
$descr = 'RH Type 0';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__rh_type_0']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__rh_type_0']);
}

require 'includes/html/graphs/generic_stats.inc.php';
