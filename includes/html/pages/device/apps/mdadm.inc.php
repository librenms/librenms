<?php

$mdadm_arrays = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'mdadm');

print_optionbar_start();

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'mdadm',
];

$array_list = [];

foreach ($mdadm_arrays as $label) {
    $array = $label;

    if ($vars['array'] == $array) {
        $label = sprintf('⚫ %s', $label);
    }

    array_push($array_list, generate_link($label, $link_array, ['array' => $array]));
}

printf('%s | arrays: %s', generate_link('All RAID Arrays', $link_array), implode(', ', $array_list));

print_optionbar_end();

$graphs = [
    'mdadm_level'          => 'RAID level',
    'mdadm_size'           => 'RAID Size',
    'mdadm_disc_count'     => 'RAID Disc count',
    'mdadm_hotspare_count' => 'RAID Hotspare Disc count',
    'mdadm_degraded'       => 'RAID degraded',
    'mdadm_sync_speed'     => 'RAID Sync speed',
    'mdadm_sync_completed' => 'RAID Sync completed',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['array'])) {
        $graph_array['array'] = $vars['array'];
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
