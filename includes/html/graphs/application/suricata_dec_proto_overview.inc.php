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
    $decoder__arp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__arp']);
    $decoder__chdlc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__chdlc']);
    $decoder__erspan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__erspan']);
    $decoder__esp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__esp']);
    $decoder__ethernet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ethernet']);
    $decoder__geneve_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__geneve']);
    $decoder__gre_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__gre']);
    $decoder__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__icmpv4']);
    $decoder__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__icmpv6']);
    $decoder__ieee8021ah_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ieee8021ah']);
    $decoder__invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__invalid']);
    $decoder__ipv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ipv4']);
    $decoder__ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ipv6']);
    $decoder__ipv4_in_ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ipv4_in_ipv6']);
    $decoder__ipv6_in_ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ipv6_in_ipv6']);
    $decoder__mpls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__mpls']);
    $decoder__nls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__nls']);
    $decoder__null_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__null']);
    $decoder__ppp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__ppp']);
    $decoder__pppoe_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__pppoe']);
    $decoder__raw_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__raw']);
    $decoder__sctp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__sctp']);
    $decoder__sll_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__sll']);
    $decoder__tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__tcp']);
    $decoder__teredo_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__teredo']);
    $decoder__udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__udp']);
    $decoder__vlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__vlan']);
    $decoder__vlan_qinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__vlan_qinq']);
    $decoder__vntag_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__vntag']);
    $decoder__vxlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['sinstance'] . '___decoder__vxlan']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    $decoder__arp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__arp']);
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
    $decoder__nls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__nls']);
    $decoder__null_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__null']);
    $decoder__ppp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__ppp']);
    $decoder__raw_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__raw']);
    $decoder__sctp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__sctp']);
    $decoder__sll_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__sll']);
    $decoder__tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__tcp']);
    $decoder__teredo_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__teredo']);
    $decoder__udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__udp']);
    $decoder__vlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vlan']);
    $decoder__vlan_qinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vlan_qinq']);
    $decoder__vlan_qinqinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vlan_qinqinq']);
    $decoder__vntag_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vntag']);
    $decoder__vxlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__vxlan']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow__udp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__icmpv4_rrd_filename,
        'descr' => '',
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
