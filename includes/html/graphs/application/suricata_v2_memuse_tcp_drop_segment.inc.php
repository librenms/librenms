<?php

$name = 'suricata';
$unit_text = 'mem drops/s';
$ds = 'data';
$descr = 'TCP Segment';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___tcp__segment_memcap_drop']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___tcp__segment_memcap_drop']);
}

require 'includes/html/graphs/generic_stats.inc.php';
