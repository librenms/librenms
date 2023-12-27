<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'portactivity',
];

print_optionbar_start();

echo 'Ports:';
$ports = $app->data['ports'] ?? [];
sort($ports);
foreach ($ports as $index => $port) {
    $label = $vars['port'] == $port
        ? '<span class="pagemenu-selected">' . $port . '</span>'
        : $port;

    echo generate_link($label, $link_array, ['port' => $port]);

    if ($index < (count($ports) - 1)) {
        echo ', ';
    }
}

print_optionbar_end();

if (! isset($vars['port'])) {
    echo "Please select a port.\n";

    $graphs = [
        // No useful bits to display with out selecting anything.
    ];
} else {
    $graphs = [
        'portactivity_totals' => 'Total Connections',
        'portactivity_total_details' => 'Total Connections Details',
        'portactivity_to' => 'Connections To Server',
        'portactivity_from' => 'Connections From Server',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['port'])) {
        $graph_array['port'] = $vars['port'];
    }

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
