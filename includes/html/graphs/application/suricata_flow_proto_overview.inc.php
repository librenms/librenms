<?php

$name = 'suricata';
$unit_text = 'flows';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
    $flow__udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__udp']);
    $flow__tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__tcp']);
    $flow__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__icmpv4']);
    $flow__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__icmpv6']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $flow__udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__udp']);
    $flow__tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__tcp']);
    $flow__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__icmpv4']);
    $flow__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__icmpv6']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow__udp_rrd_filename)){
    $rrd_list[] = [
        'filename' => $flow__icmpv4_rrd_filename,
        'descr' => 'ICMPv4',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__icmpv6_rrd_filename,
        'descr' => 'ICMPv6',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__tcp_rrd_filename,
        'descr' => 'TCP',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__udp_rrd_filename,
        'descr' => 'UDP',
        'ds' => 'data',
    ];
} elseif (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'ICMPv4',
        'ds' => 'f_icmpv4',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'ICMPv6',
        'ds' => 'f_icmpv6',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TCP',
        'ds' => 'f_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'UDP',
        'ds' => 'f_udp',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
