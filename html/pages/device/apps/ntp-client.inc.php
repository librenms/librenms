<?php

global $config;

$graphs = array(
    'ntp-client_stats' => 'NTP Client - Statistics',
    'ntp-client_freq' => 'NTP Client - Frequency',
);

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = $config['time']['now'];
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    print_optionbar_start();
    echo "<span style='font-weight: bold;'>" . $text . "</span>";
    print_optionbar_end();

    echo '<div class="row">';
    include 'includes/print-graphrow.inc.php';
    echo '</div>';

}
