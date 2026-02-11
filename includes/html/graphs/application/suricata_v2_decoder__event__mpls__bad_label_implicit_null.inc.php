<?php

$name = 'suricata';
$unit_text = 'MPLS pkts/s';
$descr = 'Bad Lbl Imp Null';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__mpls__bad_label_implicit_null']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__mpls__bad_label_implicit_null']);
}

require 'includes/html/graphs/generic_stats.inc.php';
