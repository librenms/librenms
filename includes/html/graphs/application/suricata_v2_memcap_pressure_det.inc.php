<?php

$name = 'suricata';
$unit_text = 'pressure';
$ds = 'data';
$descr = 'Memcap';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___memcap_pressure']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___memcap_pressure']);
}

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $flow__memuse_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
