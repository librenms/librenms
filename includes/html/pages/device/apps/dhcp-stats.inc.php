<?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'dhcp-stats',
];

print_optionbar_start();

echo generate_link('General', $link_array);
echo ' | ' . generate_link('Leases', $link_array, ['app_page'=>'leases']);

print_optionbar_end();


if (!isset($vars['app_page'])) {
    $graphs = [
        'dhcp-stats_stats' => 'Stats',
        'dhcp-stats_pools_percent' => 'Pools Percent',
        'dhcp-stats_pools_current' => 'Pools Current',
        'dhcp-stats_pools_max'     => 'Pools Max',
        'dhcp-stats_networks_percent' => 'Networks Percent',
        'dhcp-stats_networks_current' => 'Networks Current',
        'dhcp-stats_networks_max'     => 'Networks Max',
    ];
} elseif (isset($vars['app_page']) && $vars['app_page'] == 'leases') {
    $leases = $app->data['found_leases'] ?? [];
    $table_info = [
        'headers' => [
            'IP',
            'State',
            'HW Address',
            'Starts',
            'Ends',
            'Client Hostname',
            'Vendor',
        ],
        'rows' => [],
    ];
    foreach ($leases as $key => $lease) {
        if ($lease['client_hostname'] != '') {
            $lease['client_hostname'] = base64_decode($lease['client_hostname']);
        }
        if ($lease['vendor_class_identifier'] != '') {
            $lease['vendor_class_identifier'] = base64_decode($lease['vendor_class_identifier']);
        }
        $table_info['rows'][$key]=[
            $lease['ip'],
            $lease['state'],
            $lease['hw_address'],
            $lease['starts'],
            $lease['ends'],
            $lease['client_hostname'],
            $lease['vendor_class_identifier'],
        ];
    }
    echo render_table($table_info);
}

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
