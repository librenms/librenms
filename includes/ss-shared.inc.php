<?php

// Global variable used by the ss application to
// build graphs, rrd names and descriptions, and
// parse the ss.py script results.

// List of main sections.
$ss_section_list = ['dccp', 'inet', 'inet6', 'link', 'mptcp', 'netlink', 'raw', 'sctp', 'tcp', 'tipc', 'udp', 'unix', 'vsock', 'xdp'];

// List of all socket types.
$ss_socket_list = ['dccp', 'icmp6', 'mptcp', 'p_dgr', 'p_raw', 'raw', 'sctp', 'tcp', 'ti_dg', 'ti_rd', 'ti_sq', 'ti_st', 'u_dgr', 'u_seq', 'u_str', 'udp', 'v_dgr', 'v_str', 'xdp', 'unknown'];

// List of all address families.
$ss_af_list = ['inet', 'inet6', 'link', 'netlink', 'tipc', 'unix', 'vsock'];

// List all socket states mapped to all socket types.
$ss_socket_states_mapper = [
    'default' => [
        'ESTAB' => 'established',
        'LISTEN' => 'listening',
        'TOTAL' => 'total',
        'UNCONN' => 'unconnected',
        'UNKNOWN' => 'unknown',
    ],
    'mptcp' => [
        'CLOSE-WAIT' => 'close-wait',
        'CLOSING' => 'closing',
        'ESTAB' => 'established',
        'FIN-WAIT-1' => 'fin-wait-1',
        'FIN-WAIT-2' => 'fin-wait-2',
        'LAST-ACK' => 'last-ack',
        'LISTEN' => 'listening',
        'SYN-SENT' => 'syn-sent',
        'SYN-RECV' => 'syn-recv',
        'TIME-WAIT' => 'time-wait',
        'TOTAL' => 'total',
        'UNCONN' => 'unconnected',
        'UNKNOWN' => 'unknown',
    ],
    'sctp' => [
        'ACK_SENT' => 'ack-sent',
        'CLOSED' => 'closed',
        'COOKIE_ECHOED' => 'cookie-echoed',
        'COOKIE_WAIT' => 'cookie-wait',
        'ESTAB' => 'established',
        'SHUTDOWN_PENDING' => 'shutdown-pending',
        'SHUTDOWN_RECEIVED' => 'shutdown-received',
        'SHUTDOWN_SENT' => 'shutdown-sent',
        'TOTAL' => 'total',
    ],
    'tcp' => [
        'CLOSE-WAIT' => 'close-wait',
        'CLOSING' => 'closing',
        'ESTAB' => 'established',
        'FIN-WAIT-1' => 'fin-wait-1',
        'FIN-WAIT-2' => 'fin-wait-2',
        'LAST-ACK' => 'last-ack',
        'LISTEN' => 'listening',
        'SYN-SENT' => 'syn-sent',
        'SYN-RECV' => 'syn-recv',
        'TIME-WAIT' => 'time-wait',
        'TOTAL' => 'total',
        'UNCONN' => 'unconnected',
        'UNKNOWN' => 'unknown',
    ],
];

// Mapping of address families w/netids.  Note that
// "unknown" is output as "???" by the ss.py application.
$ss_netid_mapper = [
    'inet' => [
        'dccp',
        'mptcp',
        'raw',
        'sctp',
        'tcp',
        'udp',
        'unknown',
    ],
    'inet6' => [
        'dccp',
        'icmp6',
        'mptcp',
        'raw',
        'sctp',
        'tcp',
        'udp',
        'unknown',
    ],
    'link' => [
        'p_dgr',
        'p_raw',
        'unknown',
    ],
    'tipc' => [
        'ti_dg',
        'ti_rd',
        'ti_sq',
        'ti_st',
        'unknown',
    ],
    'unix' => [
        'u_dgr',
        'u_seq',
        'u_str',
    ],
    'vsock' => [
        'v_dgr',
        'v_str',
        'unknown',
    ],
];
