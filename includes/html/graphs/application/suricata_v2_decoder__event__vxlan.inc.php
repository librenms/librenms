<?php

$name = 'suricata';
$unit_text = 'VXLAN packets/s';
$descr = 'Unknown Payload Type';
$descr_len = 20;
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__vxlan__unknown_payload_type']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__vxlan__unknown_payload_type']);
}

require 'includes/html/graphs/generic_stats.inc.php';
