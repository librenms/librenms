<?php

$name = 'suricata';
$unit_text = 'IPv4 pkts/s';
$descr = 'Frag Pkt Too Lrg';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__frag_pkt_too_large']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__frag_pkt_too_large']);
}

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
