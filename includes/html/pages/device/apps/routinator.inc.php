<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'routinator',
];

print_optionbar_start();

echo generate_link('All RTR clients', $link_array);

$clients = $app->data['clients'] ?? [];
sort($clients);
if (! empty($clients)) {
    echo ' | RTR clients: ';
    foreach ($clients as $index => $client) {
        $label = (isset($vars['client']) && $vars['client'] == $client)
            ? '<span class="pagemenu-selected">' . $client . '</span>'
            : $client;

        echo generate_link($label, $link_array, ['client' => $client]);

        if ($index < (count($clients) - 1)) {
            echo ', ';
        }
    }
}

print_optionbar_end();

// The global / repository / RIR graphs only make sense in the overview; when a
// single RTR client is selected we just drill into that client's graphs.
$graphs = [];
if (! isset($vars['client'])) {
    $graphs = array_merge($graphs, [
        'routinator_vrps' => 'Validated ROA Payloads (VRPs)',
        'routinator_update' => 'Validation run age &amp; duration',
        'routinator_serial' => 'Serial number',
        'routinator_stale' => 'Stale objects',
        'routinator_rrdp' => 'RRDP repository status',
        'routinator_rsync' => 'rsync repository status',
        'routinator_repo_duration' => 'Repository fetch duration (max)',
        'routinator_rtr_connections' => 'RTR connections',
        'routinator_rtr_bytes' => 'RTR traffic',
        'routinator_tal_vrps' => 'VRPs per RIR',
        'routinator_tal_pubpoints' => 'Rejected publication points per RIR',
        'routinator_tal_invalid' => 'Invalid objects per RIR',
        'routinator_tal_manifests' => 'Missing manifests per RIR',
    ]);
}

if (! empty($clients)) {
    $graphs = array_merge($graphs, [
        'routinator_client_serial_lag' => 'RTR client serial lag',
        'routinator_client_update' => 'RTR client last update (seconds)',
        'routinator_client_bytes' => 'RTR client bytes written',
        'routinator_client_queries' => 'RTR client serial queries',
    ]);
}

foreach ($graphs as $key => $text) {
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['client'])) {
        $graph_array['client'] = $vars['client'];
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
