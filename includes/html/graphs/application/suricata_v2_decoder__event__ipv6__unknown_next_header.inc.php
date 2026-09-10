<?php

$name = 'suricata';
$unit_text = 'IPv6 pkts/s';
$descr = 'Unknown Next Header';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__unknown_next_header']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__unknown_next_header']);
}

require 'includes/html/graphs/generic_stats.inc.php';
