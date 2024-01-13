<?php

if ($device['os'] == 'zyxelwlc' || $device['os_group'] == 'zyxelwlc') {
    $graph_title = 'Firewall Sessions';
    $graph_type = 'active_sessions';

    include 'includes/html/print-device-graph.php';
}