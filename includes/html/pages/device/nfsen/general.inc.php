<?php

$datas = [
    'Flows'   => 'nfsen_flows',
    'Packets' => 'nfsen_packets',
    'Traffic' => 'nfsen_traffic',
];

foreach ($datas as $name => $type) {
    $graph_title = $name;
    $graph_type = 'device_' . $type;

    include 'includes/html/print-device-graph.php';
}
