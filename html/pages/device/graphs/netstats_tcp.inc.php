<?php

if (is_file(rrd_name($device['hostname'], 'netstats-tcp'))) {
    $graph_title = 'TCP Statistics';
    $graph_type  = 'device_tcp';

    include 'includes/print-device-graph.php';
}
