<?php

$name = 'suricata';
$unit_text = 'bytes';
$ds = 'data';
$descr = 'HTTP Memuse';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___http__memuse']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___http__memuse']);
}

require 'includes/html/graphs/generic_stats.inc.php';
