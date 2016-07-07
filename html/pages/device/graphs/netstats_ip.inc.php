<?php

if (is_file(rrd_name($device['hostname'], 'netstats-ip'))) {
    $graph_title = 'IP Statistics';
    $graph_type  = 'device_ip';

    include 'includes/print-device-graph.php';

    $graph_title = 'IP Fragmented Statistics';
    $graph_type  = 'device_ip_fragmented';

    include 'includes/print-device-graph.php';
}
