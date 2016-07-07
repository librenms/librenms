<?php

if (is_file(rrd_name($device['hostname'], 'netstats-ip_forward'))) {
    $graph_title = 'IP Forward statistics';
    $graph_type  = 'device_ip_forward';

    include 'includes/print-device-graph.php';
}
