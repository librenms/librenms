<?php

$name = 'suricata';
$unit_text = 'IPv6 pkts/s';
$descr = 'IP4 in IP6 Wrong Ver';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__ipv4_in_ipv6_wrong_version']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__ipv4_in_ipv6_wrong_version']);
}

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
