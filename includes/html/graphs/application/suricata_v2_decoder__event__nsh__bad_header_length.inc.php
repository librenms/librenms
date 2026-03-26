<?php

$name = 'suricata';
$unit_text = 'NSH pkts/s';
$descr = 'Bad Hdr Len';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__nsh__bad_header_length']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__nsh__bad_header_length']);
}

require 'includes/html/graphs/generic_stats.inc.php';
