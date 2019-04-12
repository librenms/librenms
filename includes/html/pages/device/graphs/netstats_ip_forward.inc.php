<?php

if (rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'netstats-ip_forward'))) {
    $graph_title = 'IP Forward statistics';
    $graph_type  = 'device_ip_forward';

    include 'includes/html/print-device-graph.php';
}
