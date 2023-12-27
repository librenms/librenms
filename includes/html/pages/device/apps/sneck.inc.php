<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'sneck',
];

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'sneck',
];

$graphs = [
    'sneck_results' => 'Results',
    'sneck_time' => 'Time Difference',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app->app_id;
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

// print any alerts if found
$sneck_data = $app->app_id;
if (isset($sneck_data)) {
    print_optionbar_start();
    echo 'Last Return...<br>';
    echo "<b>Alert(s):</b><br>\n";
    echo str_replace("\n", "<br>\n", $app->data['data']['alertString']) . "<br><br>\n";
    echo "<b>Raw JSON:</b><br>\n";
    echo "<pre>\n" . json_encode($app->data, JSON_PRETTY_PRINT) . "</pre>\n";
    print_optionbar_end();
}
