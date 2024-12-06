<?php

$name = 'suricata';
$unit_text = 'bytes';
$ds = 'data';
$descr = 'HTTP Memuse';

if (isset($vars['sinstance'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___http__memuse']);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memuse']);
}

if (! Rrd::checkRrdExists($filename)) {
    d_echo('RRD "' . $flow__memuse_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
