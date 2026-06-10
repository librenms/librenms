<?php

$name = 'suricata';
$unit_text = 'pressure';
$ds = 'data';
$descr = 'Memcap';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___memcap_pressure']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___memcap_pressure']);
}

require 'includes/html/graphs/generic_stats.inc.php';
