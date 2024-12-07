<?php

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], 'netstats-icmp'))) {
    $graph_title = 'ICMP Statistics';
    $graph_type = 'device_icmp';

    include 'includes/html/print-device-graph.php';
}
