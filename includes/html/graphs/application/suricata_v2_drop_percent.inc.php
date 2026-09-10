<?php

$name = 'suricata';
$unit_text = 'Packets';
$descr = 'Drop Prct';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___drop_percent']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___drop_percent']);
}

require 'includes/html/graphs/generic_stats.inc.php';
