<?php

if ($device['os'] == 'screenos' && Rrd::checkRrdExists(Rrd::name($device['hostname'], 'screenos-sessions'))) {
    $graph_title = 'Firewall Sessions';
    $graph_type = 'screenos_sessions';

    include 'includes/html/print-device-graph.php';
}
