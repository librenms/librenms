<?php

$name = 'suricata';
$unit_text = 'ICMPv4 pkt/s';
$colours = 'psychedelic';
$descr_len = 15;

if (isset($vars['sinstance'])) {
    $decoder__event__icmpv4__ipv4_trunc_pkt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv4__ipv4_trunc_pkt']);
    $decoder__event__icmpv4__ipv4_unknown_ver_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv4__ipv4_unknown_ver']);
    $decoder__event__icmpv4__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv4__pkt_too_small']);
    $decoder__event__icmpv4__unknown_code_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv4__unknown_code']);
    $decoder__event__icmpv4__unknown_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__icmpv4__unknown_type']);
} else {
    $decoder__event__icmpv4__ipv4_trunc_pkt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv4__ipv4_trunc_pkt']);
    $decoder__event__icmpv4__ipv4_unknown_ver_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv4__ipv4_unknown_ver']);
    $decoder__event__icmpv4__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv4__pkt_too_small']);
    $decoder__event__icmpv4__unknown_code_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv4__unknown_code']);
    $decoder__event__icmpv4__unknown_type_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__icmpv4__unknown_type']);
}

if (Rrd::checkRrdExists($decoder__event__icmpv4__ipv4_trunc_pkt_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__event__icmpv4__ipv4_trunc_pkt_rrd_filename,
        'descr' => 'IPv4 Truunc Pkt',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__icmpv4__ipv4_unknown_ver_rrd_filename,
        'descr' => 'IPv4 Unknown Ver',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__icmpv4__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__icmpv4__unknown_code_rrd_filename,
        'descr' => 'Unknown Code',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__icmpv4__unknown_type_rrd_filename,
        'descr' => 'Unknown Type',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $decoder__event__icmpv4__ipv4_trunc_pkt_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
