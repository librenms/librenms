<?php

if (rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'ucd_mem'))) {
    $graph_title = 'Memory Utilisation';
    $graph_type  = 'device_memory';

    include 'includes/html/print-device-graph.php';
}
