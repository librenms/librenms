<?php

$name = 'suricata';
$unit_text = 'ICMPv6 pkts/s';
$descr = 'MLD Msg With Inv HL';
$ds = 'data';

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv6__mld_message_with_invalid_hl']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv6__mld_message_with_invalid_hl']);
}

require 'includes/html/graphs/generic_stats.inc.php';
