<?php

if (rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'netstats-tcp'))) {
    $graph_title = 'TCP Statistics';
    $graph_type  = 'device_tcp';

    include 'includes/html/print-device-graph.php';
}
