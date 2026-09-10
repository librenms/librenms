<?php

$app_data = $app->data;

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'php-fpm',
];

if ($app_data['version'] == 'legacy') {
    $graphs = [
        'php-fpm_stats' => 'PHP-FPM',
    ];
} else {
    print_optionbar_start();

    $label = 'Totals';
    $link = generate_link($tlabel, $link_array);

    // print the link to the totals
    $link = isset($vars['phpfpm_pool'])
        ? $link
        : '<span class="pagemenu-selected">' . $link . '</span>';
    echo $link;

    // print links to the pools
    echo ' | Pools: ';
    $pools = $app->data['pools'] ?? [];
    sort($pools);
    foreach ($pools as $index => $pool_name) {
        $label = $pool_name;
        $link = generate_link($label, $link_array, ['phpfpm_pool' => $pool_name]);

        $link = $vars['phpfpm_pool'] == $pool_name
            ? '<span class="pagemenu-selected">' . $link . '</span>'
            : $link;

        echo $link;

        if ($index < (count($pools) - 1)) {
            echo ', ';
        }
    }

    print_optionbar_end();

    $graphs = [
        'php-fpm_v1_combined' => 'Combined',
        'php-fpm_v1_accepted_conn' => 'Connections Per Second',
        'php-fpm_v1_last_request_cpu' => 'Last Request CPU',
        'php-fpm_v1_slow_requests' => 'Slow Requests',
        'php-fpm_v1_active_processes' => 'Active Procs',
        'php-fpm_v1_idle_processes' => 'Idle Procs',
        'php-fpm_v1_total_processes' => 'Total Procs',
        'php-fpm_v1_max_active_processes' => 'Max Active Procs',
        'php-fpm_v1_listen_queue' => 'Listen Queue',
        'php-fpm_v1_max_listen_queue' => 'Max Listen Queue',
        'php-fpm_v1_listen_queue_len' => 'Listen Queue Len',
        'php-fpm_v1_max_children_reached' => 'Max Children Reached',
        'php-fpm_v1_start_since' => 'Uptime',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['phpfpm_pool'])) {
        $graph_array['phpfpm_pool'] = $vars['phpfpm_pool'];
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
