<?php

$name = 'suricata';
$unit_text = 'pressure';
$ds = 'data';
$descr = 'Memcap';

$rrd_prefix = isset($vars['sinstance']) ? 'instance_' . $vars['sinstance'] . '___' : 'totals___';

// Suricata 8.0 renamed memcap_pressure to memcap.pressure
$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'memcap__pressure']);
if (! Rrd::checkRrdExists($rrd_filename)) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $rrd_prefix . 'memcap_pressure']);
}

require 'includes/html/graphs/generic_stats.inc.php';
