<?php

$name = 'suricata';
$unit_text = 'IPv6 pkts/s';
$descr = 'Data After None Header';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__data_after_none_header']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__data_after_none_header']);
}

require 'includes/html/graphs/generic_stats.inc.php';
