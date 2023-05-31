<?php

$graphs = [
    'linux_softnet_stat_packets' => 'Packets Per Second',
    'linux_softnet_stat_backlog_length' => 'Backlog Lenght',
    'linux_softnet_stat_packet_dropped' => 'Packets Dropped Per Second',
    'linux_softnet_stat_cpu_collision' => 'CPU Collisions Per Second',
    'linux_softnet_stat_flow_limit' => 'Flow Limit Hit Per Second',
    'linux_softnet_stat_received_rps' => 'Received RPS Per Second',
    'linux_softnet_stat_time_squeeze' => 'Time Squeezes Per Second',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
