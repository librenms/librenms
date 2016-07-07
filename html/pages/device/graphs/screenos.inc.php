<?php

if ($device['os'] == 'screenos' && is_file(rrd_name($device['hostname'], 'screenos-sessions'))) {
    $graph_title = 'Firewall Sessions';
    $graph_type  = 'screenos_sessions';

    include 'includes/print-device-graph.php';
}
