<?php

$name = 'suricata';
$unit_text = 'GRE pkts/s';
$descr = 'Ver1 Hdr Too Big';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_hdr_too_big']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_hdr_too_big']);
}

require 'includes/html/graphs/generic_stats.inc.php';
