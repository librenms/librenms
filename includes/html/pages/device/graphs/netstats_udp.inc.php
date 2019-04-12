<?php

if (rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'netstats-udp'))) {
    $graph_title = 'UDP Statistics';
    $graph_type  = 'device_udp';

    include 'includes/html/print-device-graph.php';
}
