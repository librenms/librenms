<?php

$graphs = [
    'redis_clients'       => 'Clients',
    'redis_memory'        => 'Memory',
    'redis_commands'      => 'Commands',
    'redis_connections'   => 'Connections',
    'redis_defrag'        => 'Defrag',
    'redis_fragmentation' => 'Fragmentation',
    'redis_keyspace'      => 'Keyspace',
    'redis_net'           => 'Net',
    'redis_objects'       => 'Objects',
    'redis_sync'          => 'Sync',
    'redis_usage'         => 'Usage',
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
