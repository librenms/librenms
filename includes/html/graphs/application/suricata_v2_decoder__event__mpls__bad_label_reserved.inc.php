<?php

$name = 'suricata';
$unit_text = 'MPLS pkts/s';
$descr = 'Bad Lbl Res';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__mpls__bad_label_reserved']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__mpls__bad_label_reserved']);
}

if (Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
