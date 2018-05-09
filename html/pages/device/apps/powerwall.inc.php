<?php

global $config;

$graphs = array(
    'powerwall_summary' => 'Summary',
    'powerwall_battery-charge' => 'Battery Charge',
    'powerwall_solar-power' => 'Solar Power',
    'powerwall_load-power' => 'Load Power',
    'powerwall_battery-power' => 'Battery Power',
    'powerwall_site-power' => 'Grid Power',
    'powerwall_solar-exported' => 'Solar Exported',
    'powerwall_load-imported' => 'Load Imported',
    'powerwall_battery-imported' => 'Battery Imported',
    'powerwall_battery-exported' => 'Battery Exported',
    'powerwall_site-imported' => 'Grid Imported',
    'powerwall_site-exported' => 'Grid Exported',
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
