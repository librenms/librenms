<?php

if (rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'netstats-icmp'))) {
    $graph_title = 'ICMP Statistics';
    $graph_type  = 'device_icmp';

    include 'includes/html/print-device-graph.php';
}
