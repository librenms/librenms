<?php

if (is_file(rrd_name($device['hostname'], 'netstats-udp'))) {
    $graph_title = 'UDP Statistics';
    $graph_type  = 'device_udp';

    include 'includes/print-device-graph.php';
}
