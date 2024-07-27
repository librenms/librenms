<?php

$name = 'http_access_log_combined';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'poudriere',
];

$app_data = $app->data;

print_optionbar_start();

// print the link to the totals
$label = (isset($vars['access_log_page']) || isset($vars['log']))
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
echo generate_link($label, $link_array);
echo ' | Sets: ';

$index_int = 0;
foreach ($app_data['logs'] as $index => $log_name) {
    $label = (! isset($vars['access_log_page']) || $vars['access_log'] != $log_name)
        ? $log_name
        : '<span class="pagemenu-selected">' . $set_name . '</span>';
    $index_int++;
    echo generate_link($label, $link_array, ['log' => $log_name]);
    if (isset($app_data['logs'][$index_int])) {
        echo ', ';
    }
}

print_optionbar_end();

$graphs = [
        [
            'type' => 'bytes',
            'description' => 'Bytes, Total',
        ],
        [
            'type' => 'stats',
            'description' => 'Bytes, Stats',
        ],
        [
            'type' => 'codes_general',
            'description' => 'Response Status Codes, General',
        ],
        [
            'type' => 'codes_1xx',
            'description' => 'Response Status Codes, 1xx',
        ],
        [
            'type' => 'codes_2xx',
            'description' => 'Response Status Codes, 2xx',
        ],
        [
            'type' => 'codes_3xx',
            'description' => 'Response Status Codes, 3xx',
        ],
        [
            'type' => 'codes_4xx',
            'description' => 'Response Status Codes, 4xx',
        ],
        [
            'type' => 'codes_5xx',
            'description' => 'Response Status Codes, 5xx',
        ],
        [
            'type' => 'methods',
            'description' => 'Request Methods',
        ],
        [
            'type' => 'version',
            'description' => 'HTTP Version',
        ],
        [
            'type' => 'refer',
            'description' => 'Refer Present/Not Present',
        ],
        [
            'type' => 'user',
            'description' => 'User Present/Not Present',
        ],
        [
            'type' => 'log_size',
            'description' => 'Access Log File Size',
        ],
        [
            'type' => 'error_size',
            'description' => 'Error Log File Size',
        ],
    ];

foreach ($graphs as $key => $graph_info) {
    $graph_type = $graph_info['type'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $name . '_' . $graph_info['type'];
    if (isset($vars['log'])) {
        $graph_array['log'] = $vars['log'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph_info['description'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
