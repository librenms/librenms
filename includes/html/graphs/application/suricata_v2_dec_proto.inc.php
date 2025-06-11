<?php

$name = 'suricata';
$unit_text = 'flows';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['sinstance'])) {
    $decoder__arp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__arp']);
    $decoder__chdlc_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__chdlc']);
    $decoder__erspan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__erspan']);
    $decoder__esp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__esp']);
    $decoder__ethernet_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ethernet']);
    $decoder__geneve_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__geneve']);
    $decoder__gre_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__gre']);
    $decoder__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__icmpv4']);
    $decoder__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__icmpv6']);
    $decoder__ieee8021ah_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ieee8021ah']);
    $decoder__invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__invalid']);
    $decoder__ipv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ipv4']);
    $decoder__ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ipv6']);
    $decoder__ipv4_in_ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ipv4_in_ipv6']);
    $decoder__ipv6_in_ipv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ipv6_in_ipv6']);
    $decoder__mpls_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__mpls']);
    $decoder__nsh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__nsh']);
    $decoder__null_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__null']);
    $decoder__ppp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__ppp']);
    $decoder__pppoe_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__pppoe']);
    $decoder__raw_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__raw']);
    $decoder__sctp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__sctp']);
    $decoder__sll_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__sll']);
    $decoder__tcp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__tcp']);
    $decoder__teredo_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__teredo']);
    $decoder__too_many_layers_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__too_many_layers']);
    $decoder__udp_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__udp']);
    $decoder__vlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__vlan']);
    $decoder__vlan_qinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__vlan_qinq']);
    $decoder__vlan_qinqinq_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__vlan_qinqinq']);
    $decoder__vntag_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__vntag']);
    $decoder__vxlan_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__vxlan']);
} else {
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
}

$rrd_list = [];
if (Rrd::checkRrdExists($decoder__arp_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__arp_rrd_filename,
        'descr' => 'ARP',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__chdlc_rrd_filename,
        'descr' => 'CHDLC',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__erspan_rrd_filename,
        'descr' => 'ERSPAN',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__esp_rrd_filename,
        'descr' => 'ESP',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ethernet_rrd_filename,
        'descr' => 'Ethernet',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__geneve_rrd_filename,
        'descr' => 'Geneve',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__gre_rrd_filename,
        'descr' => 'GRE',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__icmpv4_rrd_filename,
        'descr' => 'ICMPv4',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__icmpv6_rrd_filename,
        'descr' => 'icmpv6',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ieee8021ah_rrd_filename,
        'descr' => 'IEEE 802.1ah',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__invalid_rrd_filename,
        'descr' => 'Invalid',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ipv4_rrd_filename,
        'descr' => 'IPv4',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ipv4_in_ipv6_rrd_filename,
        'descr' => 'IPv4 in IPv6',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ipv6_rrd_filename,
        'descr' => 'IPv6',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ipv6_in_ipv6_rrd_filename,
        'descr' => 'IPv6 in IPv6',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__mpls_rrd_filename,
        'descr' => 'MPLS',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__nsh_rrd_filename,
        'descr' => 'NSH',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__null_rrd_filename,
        'descr' => 'Null',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__ppp_rrd_filename,
        'descr' => 'PPP',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__pppoe_rrd_filename,
        'descr' => 'PPPoE',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__raw_rrd_filename,
        'descr' => 'Raw',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__sctp_rrd_filename,
        'descr' => 'SCTP',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__sll_rrd_filename,
        'descr' => 'SLL',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__teredo_rrd_filename,
        'descr' => 'Teredo',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__udp_rrd_filename,
        'descr' => 'udp',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__vlan_rrd_filename,
        'descr' => 'VLAN',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__vlan_qinq_rrd_filename,
        'descr' => 'VLAN QinQ',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__vlan_qinqinq_rrd_filename,
        'descr' => 'VLAN QinQinQ',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__vntag_rrd_filename,
        'descr' => 'VN-Tag',
        'ds' => 'data',
    ],
[
        'filename' => $decoder__vxlan_rrd_filename,
        'descr' => 'VXLAN',
        'ds' => 'data',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
