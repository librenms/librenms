<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'docker',
];

print_optionbar_start();

echo generate_link('All Containers', $link_array);
echo ' | Containers:';

$containers = $app->data['containers'] ?? [];
sort($containers);
foreach ($containers as $index => $container) {
    $label = $vars['container'] == $container
        ? '<span class="pagemenu-selected">' . $container . '</span>'
        : $container;

    echo generate_link($label, $link_array, ['container' => $container]);

    if ($index < (count($containers) - 1)) {
        echo ', ';
    }
}

print_optionbar_end();

$graphs = [];
if (! isset($vars['container'])) {
    $graphs = array_merge($graphs, [
        'docker_totals' => 'Totals status',
    ]);
}

$graphs = array_merge($graphs, [
    'docker_pids' => 'PIDs',
    'docker_mem_limit' => 'Container memory limit',
    'docker_mem_used' => 'Container memory used',
    'docker_cpu_usage' => 'Container CPU usage, %',
    'docker_mem_perc' => 'Container Memory usage, %',
    'docker_uptime' => 'Container uptime',
    'docker_size_rw' => 'Container Size RW',
    'docker_size_root_fs' => 'Container Size Root FS',
]);

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['container'])) {
        $graph_array['container'] = $vars['container'];
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
