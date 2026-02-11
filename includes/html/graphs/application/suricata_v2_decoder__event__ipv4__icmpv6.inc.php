<?php

$name = 'suricata';
$unit_text = 'IPv4 pkts/s';
$descr = 'ICMPv6';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__icmpv6']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__icmpv6']);
}

require 'includes/html/graphs/generic_stats.inc.php';
