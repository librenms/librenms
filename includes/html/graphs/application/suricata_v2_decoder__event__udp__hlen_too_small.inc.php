<?php

$name = 'suricata';
$unit_text = 'UDP pkts/s';
$descr = 'Hlen Too Small';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__udp__hlen_too_small']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__udp__hlen_too_small']);
}

require 'includes/html/graphs/generic_stats.inc.php';
