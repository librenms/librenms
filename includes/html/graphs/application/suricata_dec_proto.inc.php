<?php

$name = 'suricata';
$unit_text = 'packets/sec';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [];
if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IPv4',
        'ds' => 'dec_ipv4',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IPv6',
        'ds' => 'dec_ipv6',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'TCP',
        'ds' => 'dec_tcp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'UDP',
        'ds' => 'dec_udp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SCTP',
        'ds' => 'dec_sctp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'CHDLC',
        'ds' => 'dec_chdlc',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'GENEVE',
        'ds' => 'dec_geneve',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IEEE 802.1ah',
        'ds' => 'dec_ieee8021ah',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'IPv4 in IPv6',
        'ds' => 'dec_ipv4_in_ipv6',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'MPLS',
        'ds' => 'dec_mpls',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'PPP',
        'ds' => 'dec_ppp',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'PPPoE',
        'ds' => 'dec_pppoe',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'SLL',
        'ds' => 'dec_sll',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'Teredo',
        'ds' => 'dec_teredo',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'VLAN',
        'ds' => 'dec_vlan',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'VLAN Q-in-Q',
        'ds' => 'dec_vlan_qinq',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'VNTAG',
        'ds' => 'dec_vntag',
    ];
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => 'VXLAN',
        'ds' => 'dec_vxlan',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
