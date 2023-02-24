<?php

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'zfs',
];

print_optionbar_start();

echo generate_link('ARC', $link_array);
echo '| Pools:';

$pools = $app->data['pools'] ?? [];
sort($pools);
foreach ($pools as $index => $pool) {
    $label = $vars['pool'] == $pool
        ? '<span class="pagemenu-selected">' . $pool . '</span>'
        : $pool;

    echo generate_link($label, $link_array, ['pool' => $pool]);

    if ($index < (count($pools) - 1)) {
        echo ', ';
    }
}

print_optionbar_end();

if (! isset($vars['pool'])) {
    $graphs = [
        'zfs_arc_misc' => 'ARC misc',
        'zfs_arc_size' => 'ARC size in bytes',
        'zfs_arc_size_per' => 'ARC size, percent of max size',
        'zfs_arc_size_breakdown' => 'ARC size breakdown',
        'zfs_arc_efficiency' => 'ARC efficiency',
        'zfs_arc_cache_hits_by_list' => 'ARC cache hits by list',
        'zfs_arc_cache_hits_by_type' => 'ARC cache hits by type',
        'zfs_arc_cache_misses_by_type' => 'ARC cache misses by type',
        'zfs_arc_cache_hits' => 'ARC cache hits',
        'zfs_arc_cache_miss' => 'ARC cache misses',
    ];
} else {
    $graphs = [
        'zfs_pool_space'=>'Pool Space',
        'zfs_pool_cap'=>'Pool Capacity',
        'zfs_pool_frag'=>'Pool Fragmentation',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['pool'])) {
        $graph_array['pool'] = $vars['pool'];
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
