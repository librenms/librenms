<?php

global $config;

$graphs = array(
    'postgres_backends' => 'Backends',
    'postgres_cr' => 'Commits & Rollbacks',
    'postgres_rows' => 'Rows',
    'postgres_hr' => 'Buffer Hits & Disk Blocks Read',
    'postgres_index' => 'Indexs',
    'postgres_sequential' => 'Sequential',
);

foreach ($graphs as $key => $text) {
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">'.$text.'</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
