<?php

$graphs = [
    'seafile_connected'         => 'Connected Devices',
    'seafile_enabled'           => 'activated Accounts',
    'seafile_libraries'         => 'Libraries',
    'seafile_trashed_libraries' => 'Trashed Libraries',
    'seafile_size_consumption'  => 'Size Consumption',
    'seafile_groups'            => 'Groups',
    'seafile_version'           => 'Client Version',
    'seafile_platform'          => 'Client Platform',
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
