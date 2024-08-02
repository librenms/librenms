<?php

$name = 'suricata';
$unit_text = 'flows';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
$decoder__arp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__arp']);
$decoder__chdlc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__chdlc']);
$decoder__erspan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__erspan']);
$decoder__esp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__esp']);
$decoder__ethernet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ethernet']);
$decoder__geneve_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__geneve']);
$decoder__gre_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__gre']);
$decoder__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__icmpv4']);
$decoder__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__icmpv6']);
$decoder__ieee8021ah_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ieee8021ah']);
$decoder__invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__invalid']);
$decoder__ipv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ipv4']);
$decoder__ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ipv6']);
$decoder__ipv4_in_ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ipv4_in_ipv6']);
$decoder__ipv6_in_ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ipv6_in_ipv6']);
$decoder__mpls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__mpls']);
$decoder__nsh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__nsh']);
$decoder__null_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__null']);
$decoder__ppp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ppp']);
$decoder__pppoe_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__pppoe']);
$decoder__raw_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__raw']);
$decoder__sctp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__sctp']);
$decoder__sll_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__sll']);
$decoder__tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__tcp']);
$decoder__teredo_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__teredo']);
$decoder__too_many_layers_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__too_many_layers']);
$decoder__udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__udp']);
$decoder__vlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vlan']);
$decoder__vlan_qinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vlan_qinq']);
$decoder__vlan_qinqinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vlan_qinqinq']);
$decoder__vntag_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vntag']);
$decoder__vxlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vxlan']);

$rrd_list = [];
if (Rrd::checkRrdExists($decoder__arp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__arp_rrd_filename,
        'descr' => 'ARP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__chdlc_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__chdlc_rrd_filename,
        'descr' => 'CHDLC',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__erspan_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__erspan_rrd_filename,
        'descr' => 'ERSPAN',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__esp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__esp_rrd_filename,
        'descr' => 'ESP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ethernet_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ethernet_rrd_filename,
        'descr' => 'Ethernet',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__geneve_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__geneve_rrd_filename,
        'descr' => 'Geneve',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__gre_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__gre_rrd_filename,
        'descr' => 'GRE',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__icmpv4_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__icmpv4_rrd_filename,
        'descr' => 'ICMPv4',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__icmpv6_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__icmpv6_rrd_filename,
        'descr' => 'icmpv6',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ieee8021ah_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ieee8021ah_rrd_filename,
        'descr' => 'IEEE 802.1ah',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__invalid_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__invalid_rrd_filename,
        'descr' => 'Invalid',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ipv4_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ipv4_rrd_filename,
        'descr' => 'IPv4',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ipv4_in_ipv6_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ipv4_in_ipv6_rrd_filename,
        'descr' => 'IPv4 in IPv6',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ipv6_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ipv6_rrd_filename,
        'descr' => 'IPv6',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ipv6_in_ipv6_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ipv6_in_ipv6_rrd_filename,
        'descr' => 'IPv6 in IPv6',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__mpls_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__mpls_rrd_filename,
        'descr' => 'MPLS',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__nsh_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__nsh_rrd_filename,
        'descr' => 'NSH',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__null_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__null_rrd_filename,
        'descr' => 'Null',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__ppp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__ppp_rrd_filename,
        'descr' => 'PPP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__pppoe_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__pppoe_rrd_filename,
        'descr' => 'PPPoE',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__raw_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__raw_rrd_filename,
        'descr' => 'Raw',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__sctp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__sctp_rrd_filename,
        'descr' => 'SCTP',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__sll_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__sll_rrd_filename,
        'descr' => 'SLL',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__teredo_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__teredo_rrd_filename,
        'descr' => 'Teredo',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__udp_rrd_filename,
        'descr' => 'udp',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__vlan_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__vlan_rrd_filename,
        'descr' => 'VLAN',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__vlan_qinq_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__vlan_qinq_rrd_filename,
        'descr' => 'VLAN QinQ',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__vlan_qinqinq_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__vlan_qinqinq_rrd_filename,
        'descr' => 'VLAN QinQinQ',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__vntag_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__vntag_rrd_filename,
        'descr' => 'VN-Tag',
        'ds' => 'data',
    ];
}
if (Rrd::checkRrdExists($decoder__vxlan_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__vxlan_rrd_filename,
        'descr' => 'VXLAN',
        'ds' => 'data',
    ];
}
if (! isset($rrd_list[0]) && Rrd::checkRrdExists($rrd_filename)) {
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
}
if (! isset($rrd_list[0])) {
    d_echo('No RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
