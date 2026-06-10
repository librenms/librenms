<?php

$name = 'suricata';
$unit_text = 'IPv6 pkts/s';
$descr = 'Zero Len Padn';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__zero_len_padn']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__zero_len_padn']);
}

require 'includes/html/graphs/generic_stats.inc.php';
