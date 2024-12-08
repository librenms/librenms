<?php

$name = 'suricata';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'suricata',
];

if (isset($vars['sinstance'])) {
    $vars['sinstance'] = htmlspecialchars($vars['sinstance']);
}

$app_data = $app->data;

print_optionbar_start();

// set the default graph set page for v2
if (! isset($vars['suricata_graph_set'])) {
    $vars['suricata_graph_set'] = 'general';
}

// print the link to the totals
$total_label = isset($vars['sinstance'])
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
if (isset($vars['suricata_graph_set'])) {
    echo generate_link($total_label, $link_array, ['suricata_graph_set' => $vars['suricata_graph_set']]);
} else {
    echo generate_link($total_label, $link_array);
}

// print links to instances
echo ' | Instances: ';
$suricata_instances = $app->data['instances'] ?? [];
sort($suricata_instances);
foreach ($suricata_instances as $index => $sinstance) {
    $sinstance = htmlspecialchars($sinstance);
    $label = $vars['sinstance'] == $sinstance
        ? '<span class="pagemenu-selected">' . $sinstance . '</span>'
        : $sinstance;

    echo generate_link($label, $link_array, ['sinstance' => $sinstance, 'suricata_graph_set' => $vars['suricata_graph_set']]);

    if ($index < (count($suricata_instances) - 1)) {
        echo ', ';
    }
}

// print page information
// only present for v2
if ($app_data['version'] == 2) {
    echo "<br>\nPages: ";
    $suricata_pages = ['general' => 'General', 'bypassed' => 'By Passed', 'errors' => 'Errors', '#-0' => '(', 'errors_alloc' => 'Alloc', 'errors_gap' => 'Gap', 'errors_internal' => 'Internal',
        'errors_parser' => 'Parser', '#-1' => '),', 'memuse' => 'Memory Usage', '#0' => '(', 'memuse_details' => 'Details', '#1' => '),', 'detect' => ' Detect',
        'filestore' => 'File Store', 'tcp' => 'TCP', 'applayer' => 'App Layer', '#2' => '(', 'applayer_flows' => 'Flows', 'applayer_tx' => 'TX', '#2.1' => '), ',
        'decoder' => 'Decoder', '#1.1' => '(', 'decoder_erspan' => 'ERSPAN', 'decoder_gre' => 'GRE', 'decoder_icmpv4' => 'ICMPv4', 'decoder_icmpv6' => 'ICMPv6',
        'decoder_ipv4' => 'IPv4', 'decoder_ipv6' => 'IPv6', 'decoder_ltnull' => 'LT Null', 'decoder_mpls' => 'MPLS', 'decoder_nsh' => 'NSH', 'decoder_ppp' => 'PPP',
        'decoder_pppoe' => 'PPPoE', 'decoder_tcp' => 'TCP', 'decoder_udp' => 'UDP', 'decoder_vlan' => 'VLAN', 'decoder_vntag' => 'VNTag', '#3' => ')', ];

    $suricata_pages_no_comma = ['errors' => 1, 'errors_parser' => 1, 'memuse' => 1, 'memuse_details' => 1, 'applayer' => 1, 'applayer_tx' => 1, 'decoder' => 1, 'decoder_vntag' => 1];

    $page_count = 0;
    foreach ($suricata_pages as $page => $page_description) {
        if (preg_match('/^\#/', $page)) {
            echo $page_description;
        } else {
            $label = $vars['suricata_graph_set'] == $page
                ? '<span class="pagemenu-selected">' . $page_description . '</span>'
                : $page_description;

            if (isset($vars['sinstance'])) {
                echo generate_link($label, $link_array, ['sinstance' => $vars['sinstance'], 'suricata_graph_set' => $page]);
            } else {
                echo generate_link($label, $link_array, ['suricata_graph_set' => $page]);
            }

            if ($page_count < (count($suricata_pages) - 1) && ! isset($suricata_pages_no_comma[$page])) {
                echo ', ';
            }
        }
        $page_count++;
    }
}

print_optionbar_end();

if ($app_data['version'] == 1) {
    $graphs = [
        'suricata_packets' => 'Packets',
        'suricata_bytes' => 'Bytes',
        'suricata_nasty_delta' => 'Drops or Errors Delta',
        'suricata_nasty_percent' => 'Drops or Errors Percent',
        'suricata_dec_proto' => 'Decoder Protocols',
        'suricata_flow_proto' => 'Flow Protocols',
        'suricata_app_flows' => 'App Layer Flows',
        'suricata_app_tx' => 'App Layer TX',
        'suricata_mem_use' => 'Memory Usage',
        'suricata_uptime' => 'Uptime',
        'suricata_alert' => 'Alert Status',
    ];
} elseif ($app_data['version'] == 2) {
    if (! isset($vars['suricata_graph_set'])) {
        $vars['suricata_graph_set'] = 'general';
    }

    if ($vars['suricata_graph_set'] == 'general') {
        if (! isset($vars['sinstance'])) {
            // v2 computes drop_percent for total only currently
            $graphs = [
                // capture__kernel_packets
                // decoder__ethernet
                // capture__kernel_drops
                //capture__kernel_ifdrops
                'suricata_v2_packets' => 'Packets',
                // flow__icmpv4
                // flow__icmpv6
                // flow__tcp
                // flow__udp
                'suricata_v2_flow_proto' => 'Flow Protocols',
                // decoder not events, decoder__bytes, decoder__avg_pkt_size
                // decoder__bytes
                // flow_bypassed__bytes
                'suricata_v2_bytes' => 'Bytes',
                // decoder_* protocol items
                'suricata_v2_dec_proto' => 'Decoder Protocol Packets',
                // decoder__avg_pkt_size
                'suricata_v2_avg_pkg_size' => 'Decoder Average Packet Size',
                // drop_percent
                'suricata_v2_drop_percent' => 'Drop Percentage',
                // capture__kernel_ifdrops, capture__kernel_drops
                'suricata_v2_pkt_drop' => 'Packet Drop',
                // error_delta
                'suricata_v2_error_delta' => 'Error Delta',
                // app_layer__flow__*
                'suricata_v2_app_layer__flows' => 'Application Layer Flows',
                // app_layer__tx__*
                'suricata_v2_app_layer__tx' => 'Application Layer Packets',
                // flow__end__*
                'suricata_v2_flow__end' => 'Flow Ends',
                // flow__emerg_mode_entered, flow__emerg_mode_over
                'suricata_v2_flow_emerg_mode' => 'Flow Emergency Mode',
                // uptime
                'suricata_v2_uptime' => 'Uptime',
            ];
        } else {
            $graphs = [
                // capture__kernel_packets
                'suricata_v2_packets' => 'Packets',
                // decoder not events, decoder__bytes, decoder__avg_pkt_size
                // decoder__avg_pkt_size
                // decoder__bytes
                // flow_bypassed__bytes
                'suricata_v2_bytes' => 'Bytes',
                // decoder_* protocol items
                'suricata_v2_dec_proto' => 'Decoder Protocol Packets',
                // decoder__avg_pkt_size
                'suricata_v2_avg_pkg_size' => 'Decoder Average Packet Size',
                // capture__kernel_ifdrops, capture__kernel_drops
                'suricata_v2_pkt_drop' => 'Packet Drop',
                // app_layer__flow__*
                'suricata_v2_app_layer__flows' => 'Application Layer Flows',
                // app_layer__tx__*
                'suricata_v2_app_layer__tx' => 'Application Layer Packets',
                // flow__end__*
                'suricata_v2_flow__end' => 'Flow Ends',
                // flow__emerg_mode_entered, flow__emerg_mode_over
                'suricata_v2_flow_emerg_mode' => 'Flow Emergency Mode',
                // uptime
                'suricata_v2_uptime' => 'Uptime',
            ];
        }
    } elseif ($vars['suricata_graph_set'] == 'bypassed') {
        $graphs = [
            // flow_bypassed__closed
            'suricata_v2_flow_bypassed__closed' => 'Flow Bypassed Closed',
            // flow_bypassed__bytes
            'suricata_v2_flow_bypassed__bytes' => 'Flow Bypassed Bytes',
            // flow_bypassed__local_bytes
            'suricata_v2_flow_bypassed__local_bytes' => 'Flow Bypassed Local Bytes',
            // flow_bypassed__local_capture_pkts
            'suricata_v2_flow_bypassed__local_capture_pkts' => 'Flow Bypassed Local Capture Packets',
            // flow_bypassed__pkts
            'suricata_v2_flow_bypassed__pkts' => 'Flow Bypassed Packets',
            // flow_bypassed__local_pkts
            'suricata_v2_flow_bypassed__local_pkts' => 'Flow Bypassed Local Packets',
            // flow__end__state__local_bypassed
            'suricata_v2_flow__end__state__local_bypassed' => 'Flow End State Local Bypassed',
        ];
    } elseif ($vars['suricata_graph_set'] == 'errors') {
        $graphs = [
            // drop_percent
            'suricata_v2_drop_percent' => 'Drop Percent',
            // capture__kernel_ifdrops, capture__kernel_drops
            'suricata_v2_pkt_drop' => 'Packet Drop',
            // error_delta
            'suricata_v2_error_delta' => 'Error Delta',
            // app_layer__error__*__alloc
            'suricata_v2_app_layer_error_alloc' => 'App Layer Error Alloc',
            // app_layer__error__*__gap
            'suricata_v2_app_layer_error_gap' => 'App Layer Error Gap',
            // app_layer__error__*__internal
            'suricata_v2_app_layer_error_internal' => 'App Layer Error Internal',
            // app_layer__error__*__parser
            'suricata_v2_app_layer_error_parser' => 'App Layer Error Parser',
            // file_store__fs_errors
            'suricata_v2_file_store__fs_errors' => 'File Store FS Errors',
        ];
    } elseif ($vars['suricata_graph_set'] == 'memuse') {
        $graphs = [
            // flow__memuse
            // ftp__memuse
            // http__memuse
            // tcp__memuse
            // tcp__reassembly_memuse
            'suricata_v2_memuse' => 'Memory Usage',
            // flow__memcap
            // flow__memuse
            'suricata_v2_memuse_flow' => 'Flow Memory Usage',
            // ftp__memcap
            // ftp__memuse
            'suricata_v2_memuse_ftp' => 'FTP Memory Usage',
            // http__memcap
            // http__memuse
            'suricata_v2_memuse_http' => 'HTTP Memory Usage',
            // tcp__memuse
            // tcp__reassembly_memuse
            'suricata_v2_memuse_tcp' => 'TCP Memory Usage',
            // tcp__segment_memcap_drop
            // tcp__ssn_memcap_drop
            'suricata_v2_memuse_tcp_drop' => 'TCP Memcap Drop',
            // memcap_pressure
            // memcap_pressure_max
            'suricata_v2_memcap_pressure' => 'Memcap Pressure',
        ];
    } elseif ($vars['suricata_graph_set'] == 'memuse_details') {
        $graphs = [
            // ftp__memuse
            // http__memuse
            // tcp__memuse
            // tcp__reassembly_memuse
            'suricata_v2_memuse' => 'Memory Usage',
            // flow__memuse
            'suricata_v2_memuse_flow_det' => 'Flow Memory Usage',
            // ftp__memuse
            'suricata_v2_memuse_ftp_det' => 'FTP Memory Usage',
            // http__memuse
            'suricata_v2_memuse_http_det' => 'HTTP Memory Usage',
            // tcp__memuse
            'suricata_v2_memuse_tcp_det' => 'TCP Memory Usage',
            // tcp__reassembly_memuse
            'suricata_v2_memuse_tcp_reass' => 'TCP Ressaembly Memory Usage',
            // tcp__segment_memcap_drop
            'suricata_v2_memuse_tcp_drop_segment' => 'TCP Segment Memcap Drop',
            // tcp__ssn_memcap_drop
            'suricata_v2_memuse_tcp_drop_ssn' => 'TCP Session Memcap Drop',
            // memcap_pressure_max
            'suricata_v2_memcap_pressure_det' => 'Memcap Pressure',
        ];
    } elseif ($vars['suricata_graph_set'] == 'detect') {
        $graphs = [
            // detect__alert
            // detect__alerts_suppressed
            'suricata_v2_detect_alert_suppressed' => 'Detect Alert + Suppressed',
            // detect__alert
            'suricata_v2_detect_alert' => 'Detect Alert',
            // detect__alert_queue_overflow
            'suricata_v2_detect__alert_queue_overflow' => 'Detect Alert Queue Overflow',
            // detect__alerts_suppressed
            'suricata_v2_detect__alerts_suppressed' => 'Detect Alerts Suppressed',
        ];
    } elseif ($vars['suricata_graph_set'] == 'filestore') {
        $graphs = [
            // file_store__fs_errors
            'suricata_v2_file_store__fs_errors' => 'File Store FS Errors',
            // file_store__open_files
            'suricata_v2_file_store__open_files' => 'File Store Open Files',
            // file_store__open_files_max_hit
            'suricata_v2_file_store__open_files_max_hit' => 'File Store Open Files, Max Hit',
        ];
    } elseif ($vars['suricata_graph_set'] == 'tcp') {
        $graphs = [
            // tcp__ack_unseen_data
            'suricata_v2_tcp__ack_unseen_data' => 'TCP Ack Unseen Data',
            // tcp__active_sessions
            'suricata_v2_tcp__active_sessions' => 'TCP Active Sessions',
            // tcp__insert_data_normal_fail
            'suricata_v2_tcp__insert_data_normal_fail' => 'TCP Insert Data Normal Fail',
            // tcp__insert_data_overlap_fail
            'suricata_v2_tcp__insert_data_overlap_fail' => 'TCP Insert Data Overlap Fail',
            // tcp__invalid_checksum
            'suricata_v2_tcp__invalid_checksum' => 'TCP Invalid Checksum',
            // tcp__memuse
            'suricata_v2_tcp__memuse' => 'TCP Memuse',
            // tcp__midstream_pickups
            'suricata_v2_tcp__midstream_pickups' => 'TCP Midstream Pickups',
            // tcp__overlap
            'suricata_v2_tcp__overlap' => 'TCP Overlap',
            // tcp__overlap_diff_data
            'suricata_v2_tcp__overlap_diff_data' => 'TCP Overlap Diff Data',
            // tcp__pkt_on_wrong_thread
            'suricata_v2_tcp__pkt_on_wrong_thread' => 'TCP Pkt on Wrong Thread',
            // tcp__pseudo
            'suricata_v2_tcp__pseudo' => 'TCP Pseudo',
            // tcp__pseudo_failed
            'suricata_v2_tcp__pseudo_failed' => 'TCP Pseudo Failed',
            // tcp__reassembly_gap
            'suricata_v2_tcp__reassembly_gap' => 'TCP Reassembly Gap',
            // tcp__reassembly_memuse
            'suricata_v2_tcp__reassembly_memuse' => 'TCP Reassembly Memuse',
            // tcp__rst
            'suricata_v2_tcp__rst' => 'TCP Rst',
            // tcp__segment_from_cache
            'suricata_v2_tcp__segment_from_cache' => 'TCP Segment From Cache',
            // tcp__segment_from_pool
            'suricata_v2_tcp__segment_from_pool' => 'TCP Segment From Pool',
            // tcp__segment_memcap_drop
            'suricata_v2_tcp__segment_memcap_drop' => 'TCP Segment Memcap Drop',
            // tcp__sessions
            'suricata_v2_tcp__sessions' => 'TCP Sessions',
            // tcp__ssn_from_cache
            'suricata_v2_tcp__ssn_from_cache' => 'TCP SSN From Cache',
            // tcp__ssn_from_pool
            'suricata_v2_tcp__ssn_from_pool' => 'TCP SSN From Pool',
            // tcp__ssn_memcap_drop
            'suricata_v2_tcp__ssn_memcap_drop' => 'TCP SSN Memcap Drop',
            // tcp__stream_depth_reached
            'suricata_v2_tcp__stream_depth_reached' => 'TCP Stream Depth Reached',
            // tcp__syn
            'suricata_v2_tcp__syn' => 'TCP Syn',
            // tcp__synack
            'suricata_v2_tcp__synack' => 'TCP Syn ACK',
            // flow__end__tcp__*
            'suricata_v2_flow__end__end' => 'TCP Flow Ends',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder') {
        $graphs = [
            // decoder_* protocol items
            'suricata_v2_dec_proto' => 'Decoder Protocol Packets',
            // decoder__event__chdlc_*
            'suricata_v2_decoder__event__chdlc' => 'Decoder Events, CHDLC',
            // decoder__event__dce_*
            'suricata_v2_decoder__event__dce' => 'Decoder Events, DCE',
            // decoder__event__erspan_*
            'suricata_v2_decoder__event__erspan' => 'Decoder Events, ERSPAN',
            // decoder__event__esp_*
            'suricata_v2_decoder__event__esp' => 'Decoder Events, ESP',
            // decoder__event__ethernet_*
            'suricata_v2_decoder__event__ethernet' => 'Decoder Events, Ethernet',
            // decoder__event__geneve_*
            'suricata_v2_decoder__event__geneve' => 'Decoder Events, Geneve',
            // decoder__event__gre_*
            'suricata_v2_decoder__event__gre' => 'Decoder Events, GRE',
            // decoder__event__icmpv4_*
            'suricata_v2_decoder__event__icmpv4' => 'Decoder Events, ICMPv4',
            // decoder__event__icmpv6_*
            'suricata_v2_decoder__event__icmpv6' => 'Decoder Events, ICMPv6',
            // decoder__event__ieee8021ah_*
            'suricata_v2_decoder__event__ieee8021ah' => 'Decoder Events, IEEE 802.1ah',
            // decoder__event__ipraw_*
            'suricata_v2_decoder__event__ipraw' => 'Decoder Events, IP Raw',
            // decoder__event__ipv4_*
            'suricata_v2_decoder__event__ipv4' => 'Decoder Events, IPv4',
            // decoder__event__ipv6_*
            'suricata_v2_decoder__event__ipv6' => 'Decoder Events, IPv6',
            // decoder__event__ltnull_*
            'suricata_v2_decoder__event__ltnull' => 'Decoder Events, LT Null',
            // decoder__event__mpls_*
            'suricata_v2_decoder__event__mpls' => 'Decoder Events, MPLS',
            // decoder__event__nsh_*
            'suricata_v2_decoder__event__nsh' => 'Decoder Events, NSH',
            // decoder__event__ppp_*
            'suricata_v2_decoder__event__ppp' => 'Decoder Events, PPP',
            // decoder__event__pppoe_*
            'suricata_v2_decoder__event__pppoe' => 'Decoder Events, PPPoE',
            // decoder__event__sctp_*
            'suricata_v2_decoder__event__sctp' => 'Decoder Events, SCTP',
            // decoder__event__sll_*
            'suricata_v2_decoder__event__sll' => 'Decoder Events, SLL',
            // decoder__event__tcp_*
            'suricata_v2_decoder__event__tcp' => 'Decoder Events, TCP',
            // decoder__event__udp_*
            'suricata_v2_decoder__event__udp' => 'Decoder Events, UDP',
            // decoder__event__vlan_*
            'suricata_v2_decoder__event__vlan' => 'Decoder Events, VLAN',
            // decoder__event__vntag_*
            'suricata_v2_decoder__event__vntag' => 'Decoder Events, VNTag',
            // decoder__event__vxlan_*
            'suricata_v2_decoder__event__vxlan' => 'Decoder Events, VXLAN',
        ];
    } elseif ($vars['suricata_graph_set'] == 'applayer') {
        $graphs = [
            // app_layer__flow__*
            'suricata_v2_app_layer__flows' => 'Application Layer Flows',
            // app_layer__tx__*
            'suricata_v2_app_layer__tx' => 'Application Layer Packets',
        ];
    } elseif ($vars['suricata_graph_set'] == 'applayer_tx') {
        $graphs = [];
        // app_layer__tx__*
        $graphs['suricata_v2_app_layer__tx__bittorrent-dht'] = 'Bittorrent-DHT, packets/second';
        $graphs['suricata_v2_app_layer__tx__dcerpc_tcp'] = 'DCE RPC TCP, packets/second';
        $graphs['suricata_v2_app_layer__tx__dcerpc_udp'] = 'DCE RPC UDP, packets/second';
        $graphs['suricata_v2_app_layer__tx__dhcp'] = 'DHCP, packets/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__dnp3']))) {
            $graphs['suricata_v2_app_layer__tx__dnp3'] = 'DNP3, packets/second';
        }
        $graphs['suricata_v2_app_layer__tx__dns_tcp'] = 'DNS TCP, packets/second';
        $graphs['suricata_v2_app_layer__tx__dns_udp'] = 'DNS UDP, packets/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__enip_tcp']))) {
            $graphs['suricata_v2_app_layer__tx__enip_tcp'] = 'ENIP TCP, packets/second';
        }
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__enip_udp']))) {
            $graphs['suricata_v2_app_layer__tx__enip_udp'] = 'ENIP UDP, packets/second';
        }
        $graphs['suricata_v2_app_layer__tx__ftp'] = 'FTP, packets/second';
        $graphs['suricata_v2_app_layer__tx__ftp-data'] = 'FTP-DATA, packets/second';
        $graphs['suricata_v2_app_layer__tx__http'] = 'HTTP, packets/second';
        $graphs['suricata_v2_app_layer__tx__http2'] = 'HTTP2, packets/second';
        $graphs['suricata_v2_app_layer__tx__ike'] = 'IKE, packets/second';
        $graphs['suricata_v2_app_layer__tx__imap'] = 'IMAP, packets/second';
        $graphs['suricata_v2_app_layer__tx__krb5_tcp'] = 'KRB5 TCP, packets/second';
        $graphs['suricata_v2_app_layer__tx__krb5_udp'] = 'KRB5 UDP, packets/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__tx__modbus']))) {
            $graphs['suricata_v2_app_layer__tx__modbus'] = 'ModBus, packets/second';
        }
        $graphs['suricata_v2_app_layer__tx__mqtt'] = 'MQTT, packets/second';
        $graphs['suricata_v2_app_layer__tx__nfs_tcp'] = 'NFS TCP, packets/second';
        $graphs['suricata_v2_app_layer__tx__nfs_udp'] = 'NFS UDP, packets/second';
        $graphs['suricata_v2_app_layer__tx__ntp'] = 'NTP, packets/second';
        $graphs['suricata_v2_app_layer__tx__pgsql'] = 'Pgsql, packets/second';
        $graphs['suricata_v2_app_layer__tx__quic'] = 'QUIC, packets/second';
        $graphs['suricata_v2_app_layer__tx__rdp'] = 'RDP, packets/second';
        $graphs['suricata_v2_app_layer__tx__rfb'] = 'RFB, packets/second';
        $graphs['suricata_v2_app_layer__tx__sip'] = 'SIP, packets/second';
        $graphs['suricata_v2_app_layer__tx__smb'] = 'SMB, packets/second';
        $graphs['suricata_v2_app_layer__tx__smtp'] = 'SMTP, packets/second';
        $graphs['suricata_v2_app_layer__tx__snmp'] = 'SNMP, packets/second';
        $graphs['suricata_v2_app_layer__tx__ssh'] = 'SSH, packets/second';
        $graphs['suricata_v2_app_layer__tx__telnet'] = 'Telnet, packets/second';
        $graphs['suricata_v2_app_layer__tx__tftp'] = 'TFTP, packets/second';
        $graphs['suricata_v2_app_layer__tx__tls'] = 'TLS, packets/second';
    } elseif ($vars['suricata_graph_set'] == 'applayer_flows') {
        $graphs = [];
        // app_layer__flows__*
        $graphs['suricata_v2_app_layer__flow__bittorrent-dht'] = 'Bittorrent-DHT, flows/second';
        $graphs['suricata_v2_app_layer__flow__dcerpc_tcp'] = 'DCE RPC TCP, flows/second';
        $graphs['suricata_v2_app_layer__flow__dcerpc_udp'] = 'DCE RPC UDP, flows/second';
        $graphs['suricata_v2_app_layer__flow__dhcp'] = 'DHCP, flows/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__dnp3']))) {
            $graphs['suricata_v2_app_layer__flow__dnp3'] = 'DNP3, flows/second';
        }
        $graphs['suricata_v2_app_layer__flow__dns_tcp'] = 'DNS TCP, flows/second';
        $graphs['suricata_v2_app_layer__flow__dns_udp'] = 'DNS UDP, flows/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__enip_tcp']))) {
            $graphs['suricata_v2_app_layer__flow__enip_tcp'] = 'ENIP TCP, flows/second';
        }
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__enip_udp']))) {
            $graphs['suricata_v2_app_layer__flow__enip_udp'] = 'ENIP UDP, flows/second';
        }
        $graphs['suricata_v2_app_layer__flow__ftp'] = 'FTP, flows/second';
        $graphs['suricata_v2_app_layer__flow__ftp-data'] = 'FTP-DATA, flows/second';
        $graphs['suricata_v2_app_layer__flow__http'] = 'HTTP, flows/second';
        $graphs['suricata_v2_app_layer__flow__http2'] = 'HTTP2, flows/second';
        $graphs['suricata_v2_app_layer__flow__ike'] = 'IKE, flows/second';
        $graphs['suricata_v2_app_layer__flow__imap'] = 'IMAP, flows/second';
        $graphs['suricata_v2_app_layer__flow__krb5_tcp'] = 'KRB5 TCP, flows/second';
        $graphs['suricata_v2_app_layer__flow__krb5_udp'] = 'KRB5 UDP, flows/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__flow__modbus']))) {
            $graphs['suricata_v2_app_layer__flow__modbus'] = 'ModBus, flows/second';
        }
        $graphs['suricata_v2_app_layer__flow__mqtt'] = 'MQTT, flows/second';
        $graphs['suricata_v2_app_layer__flow__nfs_tcp'] = 'NFS TCP, flows/second';
        $graphs['suricata_v2_app_layer__flow__nfs_udp'] = 'NFS UDP, flows/second';
        $graphs['suricata_v2_app_layer__flow__ntp'] = 'NTP, flows/second';
        $graphs['suricata_v2_app_layer__flow__pgsql'] = 'Pgsql, flows/second';
        $graphs['suricata_v2_app_layer__flow__quic'] = 'QUIC, flows/second';
        $graphs['suricata_v2_app_layer__flow__rdp'] = 'RDP, flows/second';
        $graphs['suricata_v2_app_layer__flow__rfb'] = 'RFB, flows/second';
        $graphs['suricata_v2_app_layer__flow__sip'] = 'SIP, flows/second';
        $graphs['suricata_v2_app_layer__flow__smb'] = 'SMB, flows/second';
        $graphs['suricata_v2_app_layer__flow__smtp'] = 'SMTP, flows/second';
        $graphs['suricata_v2_app_layer__flow__snmp'] = 'SNMP, flows/second';
        $graphs['suricata_v2_app_layer__flow__ssh'] = 'SSH, flows/second';
        $graphs['suricata_v2_app_layer__flow__telnet'] = 'Telnet, flows/second';
        $graphs['suricata_v2_app_layer__flow__tftp'] = 'TFTP, flows/second';
        $graphs['suricata_v2_app_layer__flow__tls'] = 'TLS, flows/second';
    } elseif (strcmp($vars['suricata_graph_set'], 'errors_alloc') == 0) {
        $graphs = [];
        // app_layer__error__*__alloc
        $graphs['suricata_v2_app_layer__error__bittorrent-dht__alloc'] = 'Bittorrent-DHT, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_tcp__alloc'] = 'DCE RPC TCP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_udp__alloc'] = 'DCE RPC UDP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__dhcp__alloc'] = 'DHCP, alloc errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__alloc']))) {
            $graphs['suricata_v2_app_layer__error__dnp3__alloc'] = 'DNP3, alloc errors/second';
        }
        $graphs['suricata_v2_app_layer__error__dns_tcp__alloc'] = 'DNS TCP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__dns_udp__alloc'] = 'DNS UDP, alloc errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__alloc']))) {
            $graphs['suricata_v2_app_layer__error__enip_tcp__alloc'] = 'ENIP TCP, alloc errors/second';
        }
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_udp__alloc']))) {
            $graphs['suricata_v2_app_layer__error__enip_udp__alloc'] = 'ENIP UDP, alloc errors/second';
        }
        $graphs['suricata_v2_app_layer__error__ftp__alloc'] = 'FTP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__ftp-data__alloc'] = 'FTP-DATA, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__http__alloc'] = 'HTTP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__http2__alloc'] = 'HTTP2, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__ike__alloc'] = 'IKE, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__imap__alloc'] = 'IMAP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_tcp__alloc'] = 'KRB5 TCP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_udp__alloc'] = 'KRB5 UDP, alloc errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__alloc']))) {
            $graphs['suricata_v2_app_layer__error__modbus__alloc'] = 'ModBus, alloc errors/second';
        }
        $graphs['suricata_v2_app_layer__error__mqtt__alloc'] = 'MQTT, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_tcp__alloc'] = 'NFS TCP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_udp__alloc'] = 'NFS UDP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__ntp__alloc'] = 'NTP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__pgsql__alloc'] = 'Pgsql, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__quic__alloc'] = 'QUIC, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__rdp__alloc'] = 'RDP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__rfb__alloc'] = 'RFB, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__sip__alloc'] = 'SIP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__smb__alloc'] = 'SMB, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__smtp__alloc'] = 'SMTP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__snmp__alloc'] = 'SNMP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__ssh__alloc'] = 'SSH, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__telnet__alloc'] = 'Telnet, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__tftp__alloc'] = 'TFTP, alloc errors/second';
        $graphs['suricata_v2_app_layer__error__tls__alloc'] = 'TLS, alloc errors/second';
    } elseif ($vars['suricata_graph_set'] == 'errors_gap') {
        $graphs = [];
        // app_layer__error__*__gap
        $graphs['suricata_v2_app_layer__error__bittorrent-dht__gap'] = 'Bittorrent-DHT, gap errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_tcp__gap'] = 'DCE RPC TCP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__dhcp__gap'] = 'DHCP, gap errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__gap']))) {
            $graphs['suricata_v2_app_layer__error__dnp3__gap'] = 'DNP3, gap errors/second';
        }
        $graphs['suricata_v2_app_layer__error__dns_tcp__gap'] = 'DNS TCP, gap errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__gap']))) {
            $graphs['suricata_v2_app_layer__error__enip_tcp__gap'] = 'ENIP TCP, gap errors/second';
        }
        $graphs['suricata_v2_app_layer__error__ftp__gap'] = 'FTP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__ftp-data__gap'] = 'FTP-DATA, gap errors/second';
        $graphs['suricata_v2_app_layer__error__http__gap'] = 'HTTP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__http2__gap'] = 'HTTP2, gap errors/second';
        $graphs['suricata_v2_app_layer__error__ike__gap'] = 'IKE, gap errors/second';
        $graphs['suricata_v2_app_layer__error__imap__gap'] = 'IMAP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_tcp__gap'] = 'KRB5 TCP, gap errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__gap']))) {
            $graphs['suricata_v2_app_layer__error__modbus__gap'] = 'ModBus, gap errors/second';
        }
        $graphs['suricata_v2_app_layer__error__mqtt__gap'] = 'MQTT, gap errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_tcp__gap'] = 'NFS TCP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__ntp__gap'] = 'NTP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__pgsql__gap'] = 'Pgsql, gap errors/second';
        $graphs['suricata_v2_app_layer__error__quic__gap'] = 'QUIC, gap errors/second';
        $graphs['suricata_v2_app_layer__error__rdp__gap'] = 'RDP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__rfb__gap'] = 'RFB, gap errors/second';
        $graphs['suricata_v2_app_layer__error__sip__gap'] = 'SIP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__smb__gap'] = 'SMB, gap errors/second';
        $graphs['suricata_v2_app_layer__error__smtp__gap'] = 'SMTP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__snmp__gap'] = 'SNMP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__ssh__gap'] = 'SSH, gap errors/second';
        $graphs['suricata_v2_app_layer__error__telnet__gap'] = 'Telnet, gap errors/second';
        $graphs['suricata_v2_app_layer__error__tftp__gap'] = 'TFTP, gap errors/second';
        $graphs['suricata_v2_app_layer__error__tls__gap'] = 'TLS, gap errors/second';
        // tcp__reassembly_gap
        $graphs['suricata_v2_tcp__reassembly_gap'] = 'TCP Reassembly, gap errors/second';
    } elseif ($vars['suricata_graph_set'] == 'errors_internal') {
        $graphs = [];
        // app_layer__error__*__alloc
        $graphs['suricata_v2_app_layer__error__bittorrent-dht__internal'] = 'Bittorrent-DHT, internal errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_tcp__internal'] = 'DCE RPC TCP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_udp__internal'] = 'DCE RPC UDP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__dhcp__internal'] = 'DHCP, internal errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__internal']))) {
            $graphs['suricata_v2_app_layer__error__dnp3__internal'] = 'DNP3, internal errors/second';
        }
        $graphs['suricata_v2_app_layer__error__dns_tcp__internal'] = 'DNS TCP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__dns_udp__internal'] = 'DNS UDP, internal errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__internal']))) {
            $graphs['suricata_v2_app_layer__error__enip_tcp__internal'] = 'ENIP TCP, internal errors/second';
        }
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_udp__internal']))) {
            $graphs['suricata_v2_app_layer__error__enip_udp__internal'] = 'ENIP UDP, internal errors/second';
        }
        $graphs['suricata_v2_app_layer__error__ftp__internal'] = 'FTP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__ftp-data__internal'] = 'FTP-DATA, internal errors/second';
        $graphs['suricata_v2_app_layer__error__http__internal'] = 'HTTP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__http2__internal'] = 'HTTP2, internal errors/second';
        $graphs['suricata_v2_app_layer__error__ike__internal'] = 'IKE, internal errors/second';
        $graphs['suricata_v2_app_layer__error__imap__internal'] = 'IMAP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_tcp__internal'] = 'KRB5 TCP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_udp__internal'] = 'KRB5 UDP, internal errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__internal']))) {
            $graphs['suricata_v2_app_layer__error__modbus__internal'] = 'ModBus, internal errors/second';
        }
        $graphs['suricata_v2_app_layer__error__mqtt__internal'] = 'MQTT, internal errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_tcp__internal'] = 'NFS TCP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_udp__internal'] = 'NFS UDP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__ntp__internal'] = 'NTP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__pgsql__internal'] = 'Pgsql, internal errors/second';
        $graphs['suricata_v2_app_layer__error__quic__internal'] = 'QUIC, internal errors/second';
        $graphs['suricata_v2_app_layer__error__rdp__internal'] = 'RDP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__rfb__internal'] = 'RFB, internal errors/second';
        $graphs['suricata_v2_app_layer__error__sip__internal'] = 'SIP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__smb__internal'] = 'SMB, internal errors/second';
        $graphs['suricata_v2_app_layer__error__smtp__internal'] = 'SMTP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__snmp__internal'] = 'SNMP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__ssh__internal'] = 'SSH, internal errors/second';
        $graphs['suricata_v2_app_layer__error__telnet__internal'] = 'Telnet, internal errors/second';
        $graphs['suricata_v2_app_layer__error__tftp__internal'] = 'TFTP, internal errors/second';
        $graphs['suricata_v2_app_layer__error__tls__internal'] = 'TLS, internal errors/second';
    } elseif ($vars['suricata_graph_set'] == 'errors_parser') {
        $graphs = [];
        // app_layer__error__*__parser
        $graphs['suricata_v2_app_layer__error__bittorrent-dht__parser'] = 'Bittorrent-DHT, parser errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_tcp__parser'] = 'DCE RPC TCP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__dcerpc_udp__parser'] = 'DCE RPC UDP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__dhcp__parser'] = 'DHCP, parser errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__dnp3__parser']))) {
            $graphs['suricata_v2_app_layer__error__dnp3__parser'] = 'DNP3, parser errors/second';
        }
        $graphs['suricata_v2_app_layer__error__dns_tcp__parser'] = 'DNS TCP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__dns_udp__parser'] = 'DNS UDP, parser errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_tcp__parser']))) {
            $graphs['suricata_v2_app_layer__error__enip_tcp__parser'] = 'ENIP TCP, parser errors/second';
        }
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__enip_udp__parser']))) {
            $graphs['suricata_v2_app_layer__error__enip_udp__parser'] = 'ENIP UDP, parser errors/second';
        }
        $graphs['suricata_v2_app_layer__error__ftp__parser'] = 'FTP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__ftp-data__parser'] = 'FTP-DATA, parser errors/second';
        $graphs['suricata_v2_app_layer__error__http__parser'] = 'HTTP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__http2__parser'] = 'HTTP2, parser errors/second';
        $graphs['suricata_v2_app_layer__error__ike__parser'] = 'IKE, parser errors/second';
        $graphs['suricata_v2_app_layer__error__imap__parser'] = 'IMAP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_tcp__parser'] = 'KRB5 TCP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__krb5_udp__parser'] = 'KRB5 UDP, parser errors/second';
        if (Rrd::checkRrdExists(Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___app_layer__error__modbus__parser']))) {
            $graphs['suricata_v2_app_layer__error__modbus__parser'] = 'ModBus, parser errors/second';
        }
        $graphs['suricata_v2_app_layer__error__mqtt__parser'] = 'MQTT, parser errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_tcp__parser'] = 'NFS TCP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__nfs_udp__parser'] = 'NFS UDP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__ntp__parser'] = 'NTP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__pgsql__parser'] = 'Pgsql, parser errors/second';
        $graphs['suricata_v2_app_layer__error__quic__parser'] = 'QUIC, parser errors/second';
        $graphs['suricata_v2_app_layer__error__rdp__parser'] = 'RDP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__rfb__parser'] = 'RFB, parser errors/second';
        $graphs['suricata_v2_app_layer__error__sip__parser'] = 'SIP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__smb__parser'] = 'SMB, parser errors/second';
        $graphs['suricata_v2_app_layer__error__smtp__parser'] = 'SMTP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__snmp__parser'] = 'SNMP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__ssh__parser'] = 'SSH, parser errors/second';
        $graphs['suricata_v2_app_layer__error__telnet__parser'] = 'Telnet, parser errors/second';
        $graphs['suricata_v2_app_layer__error__tftp__parser'] = 'TFTP, parser errors/second';
        $graphs['suricata_v2_app_layer__error__tls__parser'] = 'TLS, parser errors/second';
    } elseif ($vars['suricata_graph_set'] == 'decoder_erspan') {
        $graphs = [
            'suricata_v2_decoder__event__erspan__header_too_small' => 'ERSPAN Header Too Small, packets/second',
            'suricata_v2_decoder__event__erspan__too_many_vlan_layers' => 'ERSPAN Too Many VLAN Layers, packets/second',
            'suricata_v2_decoder__event__erspan__unsupported_version' => 'ERSPAN Unsupported Version, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_gre') {
        $graphs = [
            'suricata_v2_decoder__event__gre__pkt_too_small' => 'GRE Packet Too Small, packets/second',
            'suricata_v2_decoder__event__gre__version0_flags' => 'GRE Version0 Flags, packets/second',
            'suricata_v2_decoder__event__gre__version0_hdr_too_big' => 'GRE Version0 Header Too Big, packets/second',
            'suricata_v2_decoder__event__gre__version0_malformed_sre_hdr' => 'GRE Version0 Malformed SRE Header, packets/second',
            'suricata_v2_decoder__event__gre__version0_recur' => 'GRE Version0 Recur, packets/second',
            'suricata_v2_decoder__event__gre__version1_chksum' => 'GRE Version1 Checksum, packets/second',
            'suricata_v2_decoder__event__gre__version1_flags' => 'GRE Version1 Flags, packets/second',
            'suricata_v2_decoder__event__gre__version1_hdr_too_big' => 'GRE Version1 Header Too Big, packets/second',
            'suricata_v2_decoder__event__gre__version1_malformed_sre_hdr' => 'GRE Version1 Malformed SRE Header, packets/second',
            'suricata_v2_decoder__event__gre__version1_no_key' => 'GRE Version1 No Key, packets/second',
            'suricata_v2_decoder__event__gre__version1_recur' => 'GRE Version1 Recur, packets/second',
            'suricata_v2_decoder__event__gre__version1_route' => 'GRE Version1 Route, packets/second',
            'suricata_v2_decoder__event__gre__version1_ssr' => 'GRE Version1 SSR, packets/second',
            'suricata_v2_decoder__event__gre__version1_wrong_protocol' => 'GRE Version1 Wrong Protocol, packets/second',
            'suricata_v2_decoder__event__gre__wrong_version' => 'GRE Wrong Version, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_icmpv4') {
        $graphs = [
            'suricata_v2_decoder__event__icmpv4__ipv4_trunc_pkt' => 'ICMPv4 Truncated Packet, packets/second',
            'suricata_v2_decoder__event__icmpv4__ipv4_unknown_ver' => 'ICMPv4 Unknown Version, packets/second',
            'suricata_v2_decoder__event__icmpv4__pkt_too_small' => 'ICMPv4 Packet Too Small, packets/second',
            'suricata_v2_decoder__event__icmpv4__unknown_code' => 'ICMPv4 Packet Too Small, packets/second',
            'suricata_v2_decoder__event__icmpv4__unknown_type' => 'ICMPv4 Unknown Type, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_icmpv6') {
        $graphs = [
            'suricata_v2_decoder__event__icmpv6__experimentation_type' => 'ICMPv6 Experimentation Type, packets/second',
            'suricata_v2_decoder__event__icmpv6__ipv6_trunc_pkt' => 'ICMPv6 Truncated Packet, packets/second',
            'suricata_v2_decoder__event__icmpv6__ipv6_unknown_version' => 'ICMPv6 Unknown Version, packets/second',
            'suricata_v2_decoder__event__icmpv6__mld_message_with_invalid_hl' => 'ICMPv6 MLD Message With Invalid HL, packets/second',
            'suricata_v2_decoder__event__icmpv6__pkt_too_small' => 'ICMPv6 Packet Too Small, packets/second',
            'suricata_v2_decoder__event__icmpv6__unassigned_type' => 'ICMPv6 Unassigned Type, packets/second',
            'suricata_v2_decoder__event__icmpv6__unknown_code' => 'ICMPv6 Unknown Code, packets/second',
            'suricata_v2_decoder__event__icmpv6__unknown_type' => 'ICMPv6 Unknown Type, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_ipv4') {
        $graphs = [
            'suricata_v2_decoder__event__ipv4__frag_ignored' => 'IPv4 Frag Ignored, packets/second',
            'suricata_v2_decoder__event__ipv4__frag_overlap' => 'IPv4 Frag Overlap, packets/second',
            'suricata_v2_decoder__event__ipv4__frag_pkt_too_large' => 'IPv4 Frag Packet Too Large, packets/second',
            'suricata_v2_decoder__event__ipv4__hlen_too_small' => 'IPv4 Hlen To Small, packets/second',
            'suricata_v2_decoder__event__ipv4__icmpv6' => 'IPv4 ICMPv6, packets/second',
            'suricata_v2_decoder__event__ipv4__iplen_smaller_than_hlen' => 'IPv4 IPlen < Hlen, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_duplicate' => 'IPv4 Opt Duplicate, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_eol_required' => 'IPv4 Opt EOL Required, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_invalid' => 'IPv4 Opt Invalid, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_invalid_len' => 'IPv4 Opt Invalid, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_malformed' => 'IPv4 Opt Malformed, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_pad_required' => 'IPv4 Pat Required, packets/second',
            'suricata_v2_decoder__event__ipv4__opt_unknown' => 'IPv4 Opt Unknown, packets/second',
            'suricata_v2_decoder__event__ipv4__pkt_too_small' => 'IPv4 Packet To Small, packets/second',
            'suricata_v2_decoder__event__ipv4__trunc_pkt' => 'IPv4 Truncated Packet, packets/second',
            'suricata_v2_decoder__event__ipv4__wrong_ip_version' => 'IPv4 Wrong IP, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_ipv6') {
        $graphs = [
            'suricata_v2_decoder__event__ipv6__data_after_none_header' => 'IPv6 After None Header, packets/second',
            'suricata_v2_decoder__event__ipv6__dstopts_only_padding' => 'IPv6 Destination Only Padding, packets/second',
            'suricata_v2_decoder__event__ipv6__dstopts_unknown_opt' => 'IPv6 Destintation Unknown Option, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_ah_res_not_null' => 'IPv6 Extension Header AH Res Not Null, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_dupl_ah' => 'IPv6 Extention Header Duplicate AH, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_dupl_dh' => 'IPv6 Extension Header Duplicate DH, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_dupl_eh' => 'IPv6 Extension Header Duplicate EH, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_dupl_fh' => 'IPv6 Extension Header Duplicate FH, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_dupl_hh' => 'IPv6 Extension Header Duplicate HH, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_dupl_rh' => 'IPv6 Extension Header Duplicate RH, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_invalid_optlen' => 'IPv6 Extension Header Invalid Option Length, packets/second',
            'suricata_v2_decoder__event__ipv6__exthdr_useless_fh' => 'IPv6 Extension Header Useless FH, packets/second',
            'suricata_v2_decoder__event__ipv6__fh_non_zero_reserved_field' => 'IPv6 FH Non-zero Reserved Field, packets/second',
            'suricata_v2_decoder__event__ipv6__frag_ignored' => 'IPv6 Frag Ignored, packets/second',
            'suricata_v2_decoder__event__ipv6__frag_invalid_length' => 'IPv6 Frag Invalid Length, packets/second',
            'suricata_v2_decoder__event__ipv6__frag_overlap' => 'IPv6 Frag Overlap, packets/second',
            'suricata_v2_decoder__event__ipv6__frag_pkt_too_large' => 'IPv6 Frag Packet Too Large, packets/second',
            'suricata_v2_decoder__event__ipv6__hopopts_only_padding' => 'IPv6 Hop Options Only Padding, packets/second',
            'suricata_v2_decoder__event__ipv6__hopopts_unknown_opt' => 'IPv6 Hop Options Unknown Option, packets/second',
            'suricata_v2_decoder__event__ipv6__icmpv4' => 'IPv6 ICMPv4, packets/second',
            'suricata_v2_decoder__event__ipv6__ipv4_in_ipv6_too_small' => 'IPv6 IPv4 in IPv6 Too Small, packets/second',
            'suricata_v2_decoder__event__ipv6__ipv4_in_ipv6_wrong_version' => 'IPv6 IPv4 in IPv6 Wrong Version, packets/second',
            'suricata_v2_decoder__event__ipv6__ipv6_in_ipv6_too_small' => 'IPv6 IPv6 in IPv6 Too Small, packets/second',
            'suricata_v2_decoder__event__ipv6__ipv6_in_ipv6_wrong_version' => 'IPv6 IPv6 in IPv6 Wrong Version, packets/second',
            'suricata_v2_decoder__event__ipv6__pkt_too_small' => 'IPv6 Packet Too Small, packets/second',
            'suricata_v2_decoder__event__ipv6__rh_type_0' => 'IPv6 RH Type 0, packets/second',
            'suricata_v2_decoder__event__ipv6__trunc_exthdr' => 'IPv6 Truncated Extension Header, packets/second',
            'suricata_v2_decoder__event__ipv6__trunc_pkt' => 'IPv6 Truncated Packet, packets/second',
            'suricata_v2_decoder__event__ipv6__unknown_next_header' => 'IPv6 Unknown Next Header, packets/second',
            'suricata_v2_decoder__event__ipv6__wrong_ip_version' => 'IPv6 Wrong IP Version, packets/second',
            'suricata_v2_decoder__event__ipv6__zero_len_padn' => 'IPv6 Zero Length Padn, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_ltnull') {
        $graphs = [
            'suricata_v2_decoder__event__ltnull__pkt_too_small' => 'LT Null Packet Too Small, packets/second',
            'suricata_v2_decoder__event__ltnull__unsupported_type' => 'LT Null Unsupported Type, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_mpls') {
        $graphs = [
            'suricata_v2_decoder__event__mpls__bad_label_implicit_null' => 'MPLS Bad Label Implicit Null, packets/second',
            'suricata_v2_decoder__event__mpls__bad_label_reserved' => 'MPLS Bad Label Reserved, packets/second',
            'suricata_v2_decoder__event__mpls__bad_label_router_alert' => 'MPLS Bad Label Router Alert, packets/second',
            'suricata_v2_decoder__event__mpls__header_too_small' => 'MPLS Header Too Small, packets/second',
            'suricata_v2_decoder__event__mpls__pkt_too_small' => 'MPLS Packet Too Small, packets/second',
            'suricata_v2_decoder__event__mpls__unknown_payload_type' => 'MPLS Unknown Payload, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_nsh') {
        $graphs = [
            'suricata_v2_decoder__event__nsh__bad_header_length' => 'NSH Bad Header Length, packets/second',
            'suricata_v2_decoder__event__nsh__header_too_small' => 'NSH Header Too Small, packets/second',
            'suricata_v2_decoder__event__nsh__reserved_type' => 'NSH Reserved Type, packets/second',
            'suricata_v2_decoder__event__nsh__unknown_payload' => 'NSH Unknown Payload, packets/second',
            'suricata_v2_decoder__event__nsh__unsupported_type' => 'NSH Unsupported Type, packets/second',
            'suricata_v2_decoder__event__nsh__unsupported_version' => 'NSH Unsupported Version, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_ppp') {
        $graphs = [
            'suricata_v2_decoder__event__ppp__ip4_pkt_too_small' => 'PPP IPv4 Packet Too Small, packets/second',
            'suricata_v2_decoder__event__ppp__ip6_pkt_too_small' => 'PPP IPv6 Packet Too Small, packets/second',
            'suricata_v2_decoder__event__ppp__pkt_too_small' => 'PPP Packet To Small, packets/second',
            'suricata_v2_decoder__event__ppp__unsup_proto' => 'PPP Unsup Proto, packets/second',
            'suricata_v2_decoder__event__ppp__vju_pkt_too_small' => 'PPP Vju Packet Too Small, packets/second',
            'suricata_v2_decoder__event__ppp__wrong_type' => 'PPP Wrong Type, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_pppoe') {
        $graphs = [
            'suricata_v2_decoder__event__pppoe__malformed_tags' => 'PPPoE Malformed Tags, packets/second',
            'suricata_v2_decoder__event__pppoe__pkt_too_small' => 'PPPoE Packet Too Small, packets/second',
            'suricata_v2_decoder__event__pppoe__wrong_code' => 'PPPoE Wrong Code, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_tcp') {
        $graphs = [
            'suricata_v2_decoder__event__tcp__hlen_too_small' => 'TCP Hlen Too Small, packets/second',
            'suricata_v2_decoder__event__tcp__invalid_optlen' => 'TCP Invalid Opt Len, packets/second',
            'suricata_v2_decoder__event__tcp__opt_duplicate' => 'TCP Opt Duplicate, packets/second',
            'suricata_v2_decoder__event__tcp__opt_invalid_len' => 'TCP Opt Invalid Len, packets/second',
            'suricata_v2_decoder__event__tcp__pkt_too_small' => 'TCP Packet Too Small, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_udp') {
        $graphs = [
            'suricata_v2_decoder__event__udp__hlen_invalid' => 'UDP Hlen Invalid, packets/second',
            'suricata_v2_decoder__event__udp__hlen_too_small' => 'UDP Hlen Too Small, packets/second',
            'suricata_v2_decoder__event__udp__len_invalid' => 'UDP Length Invalid, packets/second',
            'suricata_v2_decoder__event__udp__pkt_too_small' => 'UDP Packet Too Small, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_vlan') {
        $graphs = [
            'suricata_v2_decoder__event__vlan__header_too_small' => 'VLAN Header Too Small, packets/second',
            'suricata_v2_decoder__event__vlan__too_many_layers' => 'VLAN Too Many Layers, packets/second',
            'suricata_v2_decoder__event__vlan__unknown_type' => 'VLAN Unknown Type, packets/second',
        ];
    } elseif ($vars['suricata_graph_set'] == 'decoder_vntag') {
        $graphs = [
            'suricata_v2_decoder__event__vntag__header_too_small' => 'VNTag Header Too Small, packets/second',
            'suricata_v2_decoder__event__vntag__unknown_type' => 'VNTag Unknown Type, packets/second',
        ];
    }
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['sinstance'])) {
        $graph_array['sinstance'] = $vars['sinstance'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
