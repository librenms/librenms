<?php

if (is_file(rrd_name($device['hostname'], 'wificlients-radio1'))) {
    $graph_title = 'Wireless clients';
    $graph_type  = 'device_wificlients';

    include 'includes/print-device-graph.php';
}
