<?php

if ($device['os'] == 'fortimail' || $device['os_group'] == 'fortimail') {
    $graph_title = 'Firewall Sessions';
    $graph_type  = 'fortimail_sessions';

    include 'includes/html/print-device-graph.php';
}
