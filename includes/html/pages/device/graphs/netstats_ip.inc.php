<?php

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], 'netstats-ip'))) {
    $graph_title = 'IP Statistics';
    $graph_type = 'device_ip';

    include 'includes/html/print-device-graph.php';

    $graph_title = 'IP Fragmented Statistics';
    $graph_type = 'device_ip_fragmented';

    include 'includes/html/print-device-graph.php';
}
