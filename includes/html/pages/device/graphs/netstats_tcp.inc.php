<?php

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], 'netstats-tcp'))) {
    $graph_title = 'TCP Statistics';
    $graph_type = 'device_tcp';

    include 'includes/html/print-device-graph.php';
}
