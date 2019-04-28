<?php

if ($device['os'] == 'screenos' && rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'screenos-sessions'))) {
    $graph_title = 'Firewall Sessions';
    $graph_type  = 'screenos_sessions';

    include 'includes/html/print-device-graph.php';
}
