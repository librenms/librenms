<?php

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], 'ipSystemStats-ipv6'))) {
    $graph_title = 'IPv6 IP Packet Statistics';
    $graph_type = 'device_ipSystemStats_v6';

    include 'includes/html/print-device-graph.php';

    $graph_title = 'IPv6 IP Fragmentation Statistics';
    $graph_type = 'device_ipSystemStats_v6_frag';

    include 'includes/html/print-device-graph.php';
}

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], 'ipSystemStats-ipv4'))) {
    $graph_title = 'IPv4 IP Packet Statistics';
    $graph_type = 'device_ipSystemStats_v4';

    include 'includes/html/print-device-graph.php';
}
