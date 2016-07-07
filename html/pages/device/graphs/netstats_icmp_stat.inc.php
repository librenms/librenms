<?php

if (is_file(rrd_name($device['hostname'], 'netstats-icmp'))) {
    $graph_title = 'ICMP Statistics';
    $graph_type  = 'device_icmp';

    include 'includes/print-device-graph.php';
}
