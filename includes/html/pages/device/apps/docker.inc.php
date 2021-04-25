<?php

$domain_list = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'docker');

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'docker',
];

$containers_list = [];

foreach ($domain_list as $label) {
    $container = $label;

    if ($vars['container'] == $container) {
        $label = sprintf('⚫ %s', $label);
    }

    array_push($containers_list, generate_link($label, $link_array, ['container' => $container]));
}

printf('%s | containers: %s', generate_link('All Containers', $link_array), implode(', ', $containers_list));

print_optionbar_end();

$graphs = [
    'docker_pids' => 'PIDs',
    'docker_mem_limit' => 'Container memory limit',
    'docker_mem_used' => 'Container memory used',
    'docker_cpu_usage' => 'Container CPU usage, %',
    'docker_mem_perc' => 'Container Memory usage, %',
];

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
