<?php

namespace LibreNMS\Applications;

use App\Facades\Rrd;
use App\Models\Application;

class Suricata extends ApplicationDefinition
{
    public const string TYPE = 'suricata';
    protected const ?string DOCS = 'Suricata';

    protected const ?array OVERVIEW = [
        'packets_overview',
        'nasty_delta_overview',
        'nasty_percent_overview',
        'dec_proto_overview',
        'flow_proto_overview',
        'app_flows_overview',
        'app_tx_overview',
        'bytes_overview',
        'mem_use_overview',
        'uptime_overview',
    ];

    protected const array GRAPHS = [
        'packets' => 'Packets',
        'bytes' => 'Bytes',
        'nasty_delta' => 'Drops or Errors Delta',
        'nasty_percent' => 'Drops or Errors Percent',
        'dec_proto' => 'Decoder Protocols',
        'flow_proto' => 'Flow Protocols',
        'app_flows' => 'App Layer Flows',
        'app_tx' => 'App Layer TX',
        'mem_use' => 'Memory Usage',
        'uptime' => 'Uptime',
        'alert' => 'Alert Status',
    ];

    protected const array SECTIONS = [
        'general' => 'General',
        'bypassed' => 'By Passed',
        'errors' => 'Errors',
        'errors_alloc' => 'Errors / Alloc',
        'errors_gap' => 'Errors / Gap',
        'errors_internal' => 'Errors / Internal',
        'errors_parser' => 'Errors / Parser',
        'memuse' => 'Memory Usage',
        'memuse_details' => 'Memory Usage / Details',
        'detect' => 'Detect',
        'filestore' => 'File Store',
        'tcp' => 'TCP',
        'applayer' => 'App Layer',
        'applayer_flows' => 'App Layer / Flows',
        'applayer_tx' => 'App Layer / TX',
        'decoder' => 'Decoder',
        'decoder_erspan' => 'Decoder / ERSPAN',
        'decoder_gre' => 'Decoder / GRE',
        'decoder_icmpv4' => 'Decoder / ICMPv4',
        'decoder_icmpv6' => 'Decoder / ICMPv6',
        'decoder_ipv4' => 'Decoder / IPv4',
        'decoder_ipv6' => 'Decoder / IPv6',
        'decoder_ltnull' => 'Decoder / LT Null',
        'decoder_mpls' => 'Decoder / MPLS',
        'decoder_nsh' => 'Decoder / NSH',
        'decoder_ppp' => 'Decoder / PPP',
        'decoder_pppoe' => 'Decoder / PPPoE',
        'decoder_tcp' => 'Decoder / TCP',
        'decoder_udp' => 'Decoder / UDP',
        'decoder_vlan' => 'Decoder / VLAN',
        'decoder_vntag' => 'Decoder / VNTag',
    ];

    protected const array GRAPH_SETS = [
        'general' => [
            'v2_packets' => 'Packets',
            'v2_flow_proto' => 'Flow Protocols',
            'v2_bytes' => 'Bytes',
            'v2_dec_proto' => 'Decoder Protocol Packets',
            'v2_avg_pkg_size' => 'Decoder Average Packet Size',
            'v2_drop_percent' => 'Drop Percentage',
            'v2_pkt_drop' => 'Packet Drop',
            'v2_error_delta' => 'Error Delta',
            'v2_app_layer__flows' => 'Application Layer Flows',
            'v2_app_layer__tx' => 'Application Layer Packets',
            'v2_flow__end' => 'Flow Ends',
            'v2_flow_emerg_mode' => 'Flow Emergency Mode',
            'v2_uptime' => 'Uptime',
        ],
        'bypassed' => [
            'v2_flow_bypassed__closed' => 'Flow Bypassed Closed',
            'v2_flow_bypassed__bytes' => 'Flow Bypassed Bytes',
            'v2_flow_bypassed__local_bytes' => 'Flow Bypassed Local Bytes',
            'v2_flow_bypassed__local_capture_pkts' => 'Flow Bypassed Local Capture Packets',
            'v2_flow_bypassed__pkts' => 'Flow Bypassed Packets',
            'v2_flow_bypassed__local_pkts' => 'Flow Bypassed Local Packets',
            'v2_flow__end__state__local_bypassed' => 'Flow End State Local Bypassed',
        ],
        'errors' => [
            'v2_drop_percent' => 'Drop Percent',
            'v2_pkt_drop' => 'Packet Drop',
            'v2_error_delta' => 'Error Delta',
            'v2_app_layer_error_alloc' => 'App Layer Error Alloc',
            'v2_app_layer_error_gap' => 'App Layer Error Gap',
            'v2_app_layer_error_internal' => 'App Layer Error Internal',
            'v2_app_layer_error_parser' => 'App Layer Error Parser',
            'v2_file_store__fs_errors' => 'File Store FS Errors',
        ],
        'memuse' => [
            'v2_memuse' => 'Memory Usage',
            'v2_memuse_flow' => 'Flow Memory Usage',
            'v2_memuse_ftp' => 'FTP Memory Usage',
            'v2_memuse_http' => 'HTTP Memory Usage',
            'v2_memuse_tcp' => 'TCP Memory Usage',
            'v2_memuse_tcp_drop' => 'TCP Memcap Drop',
            'v2_memcap_pressure' => 'Memcap Pressure',
        ],
        'memuse_details' => [
            'v2_memuse' => 'Memory Usage',
            'v2_memuse_flow_det' => 'Flow Memory Usage',
            'v2_memuse_ftp_det' => 'FTP Memory Usage',
            'v2_memuse_http_det' => 'HTTP Memory Usage',
            'v2_memuse_tcp_det' => 'TCP Memory Usage',
            'v2_memuse_tcp_reass' => 'TCP Ressaembly Memory Usage',
            'v2_memuse_tcp_drop_segment' => 'TCP Segment Memcap Drop',
            'v2_memuse_tcp_drop_ssn' => 'TCP Session Memcap Drop',
            'v2_memcap_pressure_det' => 'Memcap Pressure',
        ],
        'detect' => [
            'v2_detect_alert_suppressed' => 'Detect Alert + Suppressed',
            'v2_detect_alert' => 'Detect Alert',
            'v2_detect__alert_queue_overflow' => 'Detect Alert Queue Overflow',
            'v2_detect__alerts_suppressed' => 'Detect Alerts Suppressed',
        ],
        'filestore' => [
            'v2_file_store__fs_errors' => 'File Store FS Errors',
            'v2_file_store__open_files' => 'File Store Open Files',
            'v2_file_store__open_files_max_hit' => 'File Store Open Files, Max Hit',
        ],
        'tcp' => [
            'v2_tcp__ack_unseen_data' => 'TCP Ack Unseen Data',
            'v2_tcp__active_sessions' => 'TCP Active Sessions',
            'v2_tcp__insert_data_normal_fail' => 'TCP Insert Data Normal Fail',
            'v2_tcp__insert_data_overlap_fail' => 'TCP Insert Data Overlap Fail',
            'v2_tcp__invalid_checksum' => 'TCP Invalid Checksum',
            'v2_tcp__memuse' => 'TCP Memuse',
            'v2_tcp__midstream_pickups' => 'TCP Midstream Pickups',
            'v2_tcp__overlap' => 'TCP Overlap',
            'v2_tcp__overlap_diff_data' => 'TCP Overlap Diff Data',
            'v2_tcp__pkt_on_wrong_thread' => 'TCP Pkt on Wrong Thread',
            'v2_tcp__pseudo' => 'TCP Pseudo',
            'v2_tcp__pseudo_failed' => 'TCP Pseudo Failed',
            'v2_tcp__reassembly_gap' => 'TCP Reassembly Gap',
            'v2_tcp__reassembly_memuse' => 'TCP Reassembly Memuse',
            'v2_tcp__rst' => 'TCP Rst',
            'v2_tcp__segment_from_cache' => 'TCP Segment From Cache',
            'v2_tcp__segment_from_pool' => 'TCP Segment From Pool',
            'v2_tcp__segment_memcap_drop' => 'TCP Segment Memcap Drop',
            'v2_tcp__sessions' => 'TCP Sessions',
            'v2_tcp__ssn_from_cache' => 'TCP SSN From Cache',
            'v2_tcp__ssn_from_pool' => 'TCP SSN From Pool',
            'v2_tcp__ssn_memcap_drop' => 'TCP SSN Memcap Drop',
            'v2_tcp__stream_depth_reached' => 'TCP Stream Depth Reached',
            'v2_tcp__syn' => 'TCP Syn',
            'v2_tcp__synack' => 'TCP Syn ACK',
            'v2_flow__end__end' => 'TCP Flow Ends',
        ],
        'decoder' => [
            'v2_dec_proto' => 'Decoder Protocol Packets',
            'v2_decoder__event__chdlc' => 'Decoder Events, CHDLC',
            'v2_decoder__event__dce' => 'Decoder Events, DCE',
            'v2_decoder__event__erspan' => 'Decoder Events, ERSPAN',
            'v2_decoder__event__esp' => 'Decoder Events, ESP',
            'v2_decoder__event__ethernet' => 'Decoder Events, Ethernet',
            'v2_decoder__event__geneve' => 'Decoder Events, Geneve',
            'v2_decoder__event__gre' => 'Decoder Events, GRE',
            'v2_decoder__event__icmpv4' => 'Decoder Events, ICMPv4',
            'v2_decoder__event__icmpv6' => 'Decoder Events, ICMPv6',
            'v2_decoder__event__ieee8021ah' => 'Decoder Events, IEEE 802.1ah',
            'v2_decoder__event__ipraw' => 'Decoder Events, IP Raw',
            'v2_decoder__event__ipv4' => 'Decoder Events, IPv4',
            'v2_decoder__event__ipv6' => 'Decoder Events, IPv6',
            'v2_decoder__event__ltnull' => 'Decoder Events, LT Null',
            'v2_decoder__event__mpls' => 'Decoder Events, MPLS',
            'v2_decoder__event__nsh' => 'Decoder Events, NSH',
            'v2_decoder__event__ppp' => 'Decoder Events, PPP',
            'v2_decoder__event__pppoe' => 'Decoder Events, PPPoE',
            'v2_decoder__event__sctp' => 'Decoder Events, SCTP',
            'v2_decoder__event__sll' => 'Decoder Events, SLL',
            'v2_decoder__event__tcp' => 'Decoder Events, TCP',
            'v2_decoder__event__udp' => 'Decoder Events, UDP',
            'v2_decoder__event__vlan' => 'Decoder Events, VLAN',
            'v2_decoder__event__vntag' => 'Decoder Events, VNTag',
            'v2_decoder__event__vxlan' => 'Decoder Events, VXLAN',
        ],
        'applayer' => [
            'v2_app_layer__flows' => 'Application Layer Flows',
            'v2_app_layer__tx' => 'Application Layer Packets',
        ],
        'decoder_erspan' => [
            'v2_decoder__event__erspan__header_too_small' => 'ERSPAN Header Too Small, packets/second',
            'v2_decoder__event__erspan__too_many_vlan_layers' => 'ERSPAN Too Many VLAN Layers, packets/second',
            'v2_decoder__event__erspan__unsupported_version' => 'ERSPAN Unsupported Version, packets/second',
        ],
        'decoder_gre' => [
            'v2_decoder__event__gre__pkt_too_small' => 'GRE Packet Too Small, packets/second',
            'v2_decoder__event__gre__version0_flags' => 'GRE Version0 Flags, packets/second',
            'v2_decoder__event__gre__version0_hdr_too_big' => 'GRE Version0 Header Too Big, packets/second',
            'v2_decoder__event__gre__version0_malformed_sre_hdr' => 'GRE Version0 Malformed SRE Header, packets/second',
            'v2_decoder__event__gre__version0_recur' => 'GRE Version0 Recur, packets/second',
            'v2_decoder__event__gre__version1_chksum' => 'GRE Version1 Checksum, packets/second',
            'v2_decoder__event__gre__version1_flags' => 'GRE Version1 Flags, packets/second',
            'v2_decoder__event__gre__version1_hdr_too_big' => 'GRE Version1 Header Too Big, packets/second',
            'v2_decoder__event__gre__version1_malformed_sre_hdr' => 'GRE Version1 Malformed SRE Header, packets/second',
            'v2_decoder__event__gre__version1_no_key' => 'GRE Version1 No Key, packets/second',
            'v2_decoder__event__gre__version1_recur' => 'GRE Version1 Recur, packets/second',
            'v2_decoder__event__gre__version1_route' => 'GRE Version1 Route, packets/second',
            'v2_decoder__event__gre__version1_ssr' => 'GRE Version1 SSR, packets/second',
            'v2_decoder__event__gre__version1_wrong_protocol' => 'GRE Version1 Wrong Protocol, packets/second',
            'v2_decoder__event__gre__wrong_version' => 'GRE Wrong Version, packets/second',
        ],
        'decoder_icmpv4' => [
            'v2_decoder__event__icmpv4__ipv4_trunc_pkt' => 'ICMPv4 Truncated Packet, packets/second',
            'v2_decoder__event__icmpv4__ipv4_unknown_ver' => 'ICMPv4 Unknown Version, packets/second',
            'v2_decoder__event__icmpv4__pkt_too_small' => 'ICMPv4 Packet Too Small, packets/second',
            'v2_decoder__event__icmpv4__unknown_code' => 'ICMPv4 Packet Too Small, packets/second',
            'v2_decoder__event__icmpv4__unknown_type' => 'ICMPv4 Unknown Type, packets/second',
        ],
        'decoder_icmpv6' => [
            'v2_decoder__event__icmpv6__experimentation_type' => 'ICMPv6 Experimentation Type, packets/second',
            'v2_decoder__event__icmpv6__ipv6_trunc_pkt' => 'ICMPv6 Truncated Packet, packets/second',
            'v2_decoder__event__icmpv6__ipv6_unknown_version' => 'ICMPv6 Unknown Version, packets/second',
            'v2_decoder__event__icmpv6__mld_message_with_invalid_hl' => 'ICMPv6 MLD Message With Invalid HL, packets/second',
            'v2_decoder__event__icmpv6__pkt_too_small' => 'ICMPv6 Packet Too Small, packets/second',
            'v2_decoder__event__icmpv6__unassigned_type' => 'ICMPv6 Unassigned Type, packets/second',
            'v2_decoder__event__icmpv6__unknown_code' => 'ICMPv6 Unknown Code, packets/second',
            'v2_decoder__event__icmpv6__unknown_type' => 'ICMPv6 Unknown Type, packets/second',
        ],
        'decoder_ipv4' => [
            'v2_decoder__event__ipv4__frag_ignored' => 'IPv4 Frag Ignored, packets/second',
            'v2_decoder__event__ipv4__frag_overlap' => 'IPv4 Frag Overlap, packets/second',
            'v2_decoder__event__ipv4__frag_pkt_too_large' => 'IPv4 Frag Packet Too Large, packets/second',
            'v2_decoder__event__ipv4__hlen_too_small' => 'IPv4 Hlen To Small, packets/second',
            'v2_decoder__event__ipv4__icmpv6' => 'IPv4 ICMPv6, packets/second',
            'v2_decoder__event__ipv4__iplen_smaller_than_hlen' => 'IPv4 IPlen < Hlen, packets/second',
            'v2_decoder__event__ipv4__opt_duplicate' => 'IPv4 Opt Duplicate, packets/second',
            'v2_decoder__event__ipv4__opt_eol_required' => 'IPv4 Opt EOL Required, packets/second',
            'v2_decoder__event__ipv4__opt_invalid' => 'IPv4 Opt Invalid, packets/second',
            'v2_decoder__event__ipv4__opt_invalid_len' => 'IPv4 Opt Invalid, packets/second',
            'v2_decoder__event__ipv4__opt_malformed' => 'IPv4 Opt Malformed, packets/second',
            'v2_decoder__event__ipv4__opt_pad_required' => 'IPv4 Pat Required, packets/second',
            'v2_decoder__event__ipv4__opt_unknown' => 'IPv4 Opt Unknown, packets/second',
            'v2_decoder__event__ipv4__pkt_too_small' => 'IPv4 Packet To Small, packets/second',
            'v2_decoder__event__ipv4__trunc_pkt' => 'IPv4 Truncated Packet, packets/second',
            'v2_decoder__event__ipv4__wrong_ip_version' => 'IPv4 Wrong IP, packets/second',
        ],
        'decoder_ipv6' => [
            'v2_decoder__event__ipv6__data_after_none_header' => 'IPv6 After None Header, packets/second',
            'v2_decoder__event__ipv6__dstopts_only_padding' => 'IPv6 Destination Only Padding, packets/second',
            'v2_decoder__event__ipv6__dstopts_unknown_opt' => 'IPv6 Destintation Unknown Option, packets/second',
            'v2_decoder__event__ipv6__exthdr_ah_res_not_null' => 'IPv6 Extension Header AH Res Not Null, packets/second',
            'v2_decoder__event__ipv6__exthdr_dupl_ah' => 'IPv6 Extention Header Duplicate AH, packets/second',
            'v2_decoder__event__ipv6__exthdr_dupl_dh' => 'IPv6 Extension Header Duplicate DH, packets/second',
            'v2_decoder__event__ipv6__exthdr_dupl_eh' => 'IPv6 Extension Header Duplicate EH, packets/second',
            'v2_decoder__event__ipv6__exthdr_dupl_fh' => 'IPv6 Extension Header Duplicate FH, packets/second',
            'v2_decoder__event__ipv6__exthdr_dupl_hh' => 'IPv6 Extension Header Duplicate HH, packets/second',
            'v2_decoder__event__ipv6__exthdr_dupl_rh' => 'IPv6 Extension Header Duplicate RH, packets/second',
            'v2_decoder__event__ipv6__exthdr_invalid_optlen' => 'IPv6 Extension Header Invalid Option Length, packets/second',
            'v2_decoder__event__ipv6__exthdr_useless_fh' => 'IPv6 Extension Header Useless FH, packets/second',
            'v2_decoder__event__ipv6__fh_non_zero_reserved_field' => 'IPv6 FH Non-zero Reserved Field, packets/second',
            'v2_decoder__event__ipv6__frag_ignored' => 'IPv6 Frag Ignored, packets/second',
            'v2_decoder__event__ipv6__frag_invalid_length' => 'IPv6 Frag Invalid Length, packets/second',
            'v2_decoder__event__ipv6__frag_overlap' => 'IPv6 Frag Overlap, packets/second',
            'v2_decoder__event__ipv6__frag_pkt_too_large' => 'IPv6 Frag Packet Too Large, packets/second',
            'v2_decoder__event__ipv6__hopopts_only_padding' => 'IPv6 Hop Options Only Padding, packets/second',
            'v2_decoder__event__ipv6__hopopts_unknown_opt' => 'IPv6 Hop Options Unknown Option, packets/second',
            'v2_decoder__event__ipv6__icmpv4' => 'IPv6 ICMPv4, packets/second',
            'v2_decoder__event__ipv6__ipv4_in_ipv6_too_small' => 'IPv6 IPv4 in IPv6 Too Small, packets/second',
            'v2_decoder__event__ipv6__ipv4_in_ipv6_wrong_version' => 'IPv6 IPv4 in IPv6 Wrong Version, packets/second',
            'v2_decoder__event__ipv6__ipv6_in_ipv6_too_small' => 'IPv6 IPv6 in IPv6 Too Small, packets/second',
            'v2_decoder__event__ipv6__ipv6_in_ipv6_wrong_version' => 'IPv6 IPv6 in IPv6 Wrong Version, packets/second',
            'v2_decoder__event__ipv6__pkt_too_small' => 'IPv6 Packet Too Small, packets/second',
            'v2_decoder__event__ipv6__rh_type_0' => 'IPv6 RH Type 0, packets/second',
            'v2_decoder__event__ipv6__trunc_exthdr' => 'IPv6 Truncated Extension Header, packets/second',
            'v2_decoder__event__ipv6__trunc_pkt' => 'IPv6 Truncated Packet, packets/second',
            'v2_decoder__event__ipv6__unknown_next_header' => 'IPv6 Unknown Next Header, packets/second',
            'v2_decoder__event__ipv6__wrong_ip_version' => 'IPv6 Wrong IP Version, packets/second',
            'v2_decoder__event__ipv6__zero_len_padn' => 'IPv6 Zero Length Padn, packets/second',
        ],
        'decoder_ltnull' => [
            'v2_decoder__event__ltnull__pkt_too_small' => 'LT Null Packet Too Small, packets/second',
            'v2_decoder__event__ltnull__unsupported_type' => 'LT Null Unsupported Type, packets/second',
        ],
        'decoder_mpls' => [
            'v2_decoder__event__mpls__bad_label_implicit_null' => 'MPLS Bad Label Implicit Null, packets/second',
            'v2_decoder__event__mpls__bad_label_reserved' => 'MPLS Bad Label Reserved, packets/second',
            'v2_decoder__event__mpls__bad_label_router_alert' => 'MPLS Bad Label Router Alert, packets/second',
            'v2_decoder__event__mpls__header_too_small' => 'MPLS Header Too Small, packets/second',
            'v2_decoder__event__mpls__pkt_too_small' => 'MPLS Packet Too Small, packets/second',
            'v2_decoder__event__mpls__unknown_payload_type' => 'MPLS Unknown Payload, packets/second',
        ],
        'decoder_nsh' => [
            'v2_decoder__event__nsh__bad_header_length' => 'NSH Bad Header Length, packets/second',
            'v2_decoder__event__nsh__header_too_small' => 'NSH Header Too Small, packets/second',
            'v2_decoder__event__nsh__reserved_type' => 'NSH Reserved Type, packets/second',
            'v2_decoder__event__nsh__unknown_payload' => 'NSH Unknown Payload, packets/second',
            'v2_decoder__event__nsh__unsupported_type' => 'NSH Unsupported Type, packets/second',
            'v2_decoder__event__nsh__unsupported_version' => 'NSH Unsupported Version, packets/second',
        ],
        'decoder_ppp' => [
            'v2_decoder__event__ppp__ip4_pkt_too_small' => 'PPP IPv4 Packet Too Small, packets/second',
            'v2_decoder__event__ppp__ip6_pkt_too_small' => 'PPP IPv6 Packet Too Small, packets/second',
            'v2_decoder__event__ppp__pkt_too_small' => 'PPP Packet To Small, packets/second',
            'v2_decoder__event__ppp__unsup_proto' => 'PPP Unsup Proto, packets/second',
            'v2_decoder__event__ppp__vju_pkt_too_small' => 'PPP Vju Packet Too Small, packets/second',
            'v2_decoder__event__ppp__wrong_type' => 'PPP Wrong Type, packets/second',
        ],
        'decoder_pppoe' => [
            'v2_decoder__event__pppoe__malformed_tags' => 'PPPoE Malformed Tags, packets/second',
            'v2_decoder__event__pppoe__pkt_too_small' => 'PPPoE Packet Too Small, packets/second',
            'v2_decoder__event__pppoe__wrong_code' => 'PPPoE Wrong Code, packets/second',
        ],
        'decoder_tcp' => [
            'v2_decoder__event__tcp__hlen_too_small' => 'TCP Hlen Too Small, packets/second',
            'v2_decoder__event__tcp__invalid_optlen' => 'TCP Invalid Opt Len, packets/second',
            'v2_decoder__event__tcp__opt_duplicate' => 'TCP Opt Duplicate, packets/second',
            'v2_decoder__event__tcp__opt_invalid_len' => 'TCP Opt Invalid Len, packets/second',
            'v2_decoder__event__tcp__pkt_too_small' => 'TCP Packet Too Small, packets/second',
        ],
        'decoder_udp' => [
            'v2_decoder__event__udp__hlen_invalid' => 'UDP Hlen Invalid, packets/second',
            'v2_decoder__event__udp__hlen_too_small' => 'UDP Hlen Too Small, packets/second',
            'v2_decoder__event__udp__len_invalid' => 'UDP Length Invalid, packets/second',
            'v2_decoder__event__udp__pkt_too_small' => 'UDP Packet Too Small, packets/second',
        ],
        'decoder_vlan' => [
            'v2_decoder__event__vlan__header_too_small' => 'VLAN Header Too Small, packets/second',
            'v2_decoder__event__vlan__too_many_layers' => 'VLAN Too Many Layers, packets/second',
            'v2_decoder__event__vlan__unknown_type' => 'VLAN Unknown Type, packets/second',
        ],
        'decoder_vntag' => [
            'v2_decoder__event__vntag__header_too_small' => 'VNTag Header Too Small, packets/second',
            'v2_decoder__event__vntag__unknown_type' => 'VNTag Unknown Type, packets/second',
        ],
    ];

    protected const array GENERAL_INSTANCE_GRAPHS = [
        'v2_packets' => 'Packets',
        'v2_bytes' => 'Bytes',
        'v2_dec_proto' => 'Decoder Protocol Packets',
        'v2_avg_pkg_size' => 'Decoder Average Packet Size',
        'v2_pkt_drop' => 'Packet Drop',
        'v2_app_layer__flows' => 'Application Layer Flows',
        'v2_app_layer__tx' => 'Application Layer Packets',
        'v2_flow__end' => 'Flow Ends',
        'v2_flow_emerg_mode' => 'Flow Emergency Mode',
        'v2_uptime' => 'Uptime',
    ];

    private const array APP_LAYER_METRICS = [
        'applayer_tx' => ['tx', 'packets/second'],
        'applayer_flows' => ['flow', 'flows/second'],
        'errors_alloc' => ['alloc', 'alloc errors/second'],
        'errors_gap' => ['gap', 'gap errors/second'],
        'errors_internal' => ['internal', 'internal errors/second'],
        'errors_parser' => ['parser', 'parser errors/second'],
    ];

    private const array PROTOCOLS = [
        'bittorrent-dht' => 'Bittorrent-DHT',
        'dcerpc_tcp' => 'DCE RPC TCP',
        'dcerpc_udp' => 'DCE RPC UDP',
        'dhcp' => 'DHCP',
        'dnp3' => 'DNP3',
        'dns_tcp' => 'DNS TCP',
        'dns_udp' => 'DNS UDP',
        'enip_tcp' => 'ENIP TCP',
        'enip_udp' => 'ENIP UDP',
        'ftp' => 'FTP',
        'ftp-data' => 'FTP-DATA',
        'http' => 'HTTP',
        'http2' => 'HTTP2',
        'ike' => 'IKE',
        'imap' => 'IMAP',
        'krb5_tcp' => 'KRB5 TCP',
        'krb5_udp' => 'KRB5 UDP',
        'modbus' => 'ModBus',
        'mqtt' => 'MQTT',
        'nfs_tcp' => 'NFS TCP',
        'nfs_udp' => 'NFS UDP',
        'ntp' => 'NTP',
        'pgsql' => 'Pgsql',
        'quic' => 'QUIC',
        'rdp' => 'RDP',
        'rfb' => 'RFB',
        'sip' => 'SIP',
        'smb' => 'SMB',
        'smtp' => 'SMTP',
        'snmp' => 'SNMP',
        'ssh' => 'SSH',
        'telnet' => 'Telnet',
        'tftp' => 'TFTP',
        'tls' => 'TLS',
    ];

    private const array OPTIONAL_PROTOCOLS = ['dnp3', 'enip_tcp', 'enip_udp', 'modbus'];
    private const array PROTOCOLS_WITHOUT_GAP_COUNTERS = ['dcerpc_udp', 'dns_udp', 'enip_udp', 'krb5_udp', 'nfs_udp'];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $instances = $data['instances'] ?? [];
        sort($instances);
        $section = $page->option('suricata_graph_set');
        if ($section === null || ! isset(self::SECTIONS[$section])) {
            $section = 'general';
        }
        $instance = $page->addResourceFilter('Instances', 'sinstance', $instances, 'Totals', ['suricata_graph_set' => $section]);

        $graphs = [];
        if (($data['version'] ?? null) == 2) {
            $page->addFilter('Pages', 'suricata_graph_set', self::SECTIONS, $section, ['sinstance' => $instance]);
            $graphs = match (true) {
                isset(self::APP_LAYER_METRICS[$section]) => $this->appLayerGraphs($application, $section),
                $section === 'general' && $instance !== null => self::GENERAL_INSTANCE_GRAPHS,
                default => self::GRAPH_SETS[$section],
            };
        } elseif (($data['version'] ?? null) == 1) {
            $graphs = self::GRAPHS;
        }
        $page->addGraphs($graphs, ['sinstance' => $instance]);
    }

    /** @return array<string, string> */
    private function appLayerGraphs(Application $application, string $section): array
    {
        [$metric, $unit] = self::APP_LAYER_METRICS[$section];
        $graphs = [];
        foreach (self::PROTOCOLS as $protocol => $label) {
            if ($metric === 'gap' && in_array($protocol, self::PROTOCOLS_WITHOUT_GAP_COUNTERS, true)) {
                continue;
            }

            $stat = match ($metric) {
                'tx', 'flow' => "app_layer__{$metric}__{$protocol}",
                default => "app_layer__error__{$protocol}__{$metric}",
            };
            if (in_array($protocol, self::OPTIONAL_PROTOCOLS, true)) {
                $rrd = Rrd::name($application->device->hostname, ['app', self::TYPE, $application->app_id, "totals___$stat"]);
                if (! Rrd::checkRrdExists($rrd)) {
                    continue;
                }
            }
            $graphs["v2_$stat"] = "$label, $unit";
        }
        if ($metric === 'gap') {
            $graphs['v2_tcp__reassembly_gap'] = 'TCP Reassembly, gap errors/second';
        }

        return $graphs;
    }
}
