<?php

$name = 'suricata';
$unit_text = 'IPv4 pkts/s';
$descr = 'IPlen < Hlen';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__iplen_smaller_than_hlen']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__iplen_smaller_than_hlen']);
}

require 'includes/html/graphs/generic_stats.inc.php';
