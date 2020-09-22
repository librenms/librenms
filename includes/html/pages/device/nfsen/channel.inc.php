<?php

$datas = [
    'Flows'   => 'nfsen_channel_flows',
    'Packets' => 'nfsen_channel_packets',
    'Traffic' => 'nfsen_channel_traffic',
];

if (isset($channelFilter) && file_exists($channelFilter)) {
    $chanelFilterContents = file_get_contents($channelFilter);
    print_optionbar_start();
    echo $vars['channel'] . ' Filter: ' . $chanelFilterContents;
    print_optionbar_end();
}

foreach ($datas as $name => $type) {
    $graph_title = $name;
    $graph_type = 'device_' . $type;
    $graph_array['channel'] = $vars['channel'];

    include 'includes/html/print-device-graph.php';
}
