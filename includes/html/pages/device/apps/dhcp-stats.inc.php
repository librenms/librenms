<?php

$graphs = [
    'dhcp-stats_stats' => 'Stats',
    'dhcp-stats_pools_percent' => 'Pools Percent',
    'dhcp-stats_pools_current' => 'Pools Current',
    'dhcp-stats_pools_max'     => 'Pools Max',
    'dhcp-stats_networks_percent' => 'Networks Percent',
    'dhcp-stats_networks_current' => 'Networks Current',
    'dhcp-stats_networks_max'     => 'Networks Max',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
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
