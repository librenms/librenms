<?php

if (rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'wificlients-radio1'))) {
    $graph_title = 'Wireless clients';
    $graph_type  = 'device_wificlients';

    include 'includes/print-device-graph.php';
}
