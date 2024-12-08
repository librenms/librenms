<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'zfs',
];

if (isset($vars['pool'])) {
    $vars['pool'] = htmlspecialchars($vars['pool']);
}

print_optionbar_start();

echo generate_link('ARC', $link_array);
echo ' | ' . generate_link('L2', $link_array, ['zfs_page' => 'l2']);
echo ' | Pools: ';

$pools = $app->data['pools'] ?? [];
$status_info = $app->data['status_info'] ?? [];
$version = $app->data['version'] ?? 2;
sort($pools);
foreach ($pools as $index => $pool) {
    $pool = htmlspecialchars($pool);
    $label = $vars['pool'] == $pool
        ? '<span class="pagemenu-selected">' . $pool . '</span>'
        : $pool;

    echo generate_link($label, $link_array, ['pool' => $pool]);

    if ($index < (count($pools) - 1)) {
        echo ', ';
    }
}

if (isset($vars['pool']) && isset($status_info[$vars['pool']])) {
    echo '<hr><tt>' . str_replace(' ', '&nbsp;', str_replace("\n", '<br>', htmlspecialchars($status_info[$vars['pool']]))) . "</tt>\n";
}

print_optionbar_end();

if (isset($vars['pool'])) {
    $graphs = [
        'zfs_pool_space' => 'Pool Space',
        'zfs_pool_cap' => 'Pool Capacity',
        'zfs_pool_frag' => 'Pool Fragmentation',
    ];
    if ($version >= 2) {
        $graphs['zfs_pool_errors'] = 'Pool Errors';
        $graphs['zfs_pool_operations'] = 'Pool Operations';
        $graphs['zfs_pool_bandwidth'] = 'Pool Bandwidth';
        $graphs['zfs_pool_total_wait'] = 'Pool Total Wait';
        $graphs['zfs_pool_disk_wait'] = 'Pool Disk Wait';
        $graphs['zfs_pool_syncq_wait'] = 'Pool SyncQ Wait';
        $graphs['zfs_pool_asyncq_wait'] = 'Pool AsyncQ Wait';
        $graphs['zfs_pool_scrub_wait'] = 'Pool Scrub Wait';
        $graphs['zfs_pool_trim_wait'] = 'Pool Trim Wait';
        $graphs['zfs_pool_syncq_read'] = 'Pool SyncQ Read';
        $graphs['zfs_pool_syncq_write'] = 'Pool SyncQ Write';
        $graphs['zfs_pool_asyncq_read'] = 'Pool AsyncQ Read';
        $graphs['zfs_pool_asyncq_write'] = 'Pool AsyncQ Write';
        $graphs['zfs_pool_scrubq_read'] = 'Pool ScrubQ Read';
        $graphs['zfs_pool_trimq_write'] = 'Pool TrimQ Write';
    }
} elseif (isset($vars['zfs_page']) && $vars['zfs_page'] == 'l2') {
    $graphs = [
        'zfs_l2_size' => 'L2 size in bytes',
        'zfs_l2_rw_bytes' => 'L2 Read And Writes Bytes Per Second',
        'zfs_l2_d_to_m_ratio' => 'L2 Data To Meta Ratio',
        'zfs_l2_access_total' => 'L2 Total Hits And Misses Per Second',
        'zfs_l2_errors' => 'L2 Errors Per Second',
        'zfs_l2_errors' => 'L2 Error Types Per Second',
        'zfs_l2_sizes' => 'L2 Sizes',
        'zfs_l2_asize' => 'L2 Asize',
        'zfs_l2_bufc_d_asize' => 'L2 BufC Data Asize',
        'zfs_l2_bufc_m_asize' => 'L2 BufC Metadata Asize',
        'zfs_l2_hdr_size' => 'L2 HDR Size',
        'zfs_l2_log_blk_asize' => 'L2 Log Blk Asize',
        'zfs_l2_mfu_asize' => 'L2 MFU Asize',
        'zfs_l2_mru_asize' => 'L2 MRU Asize',
        'zfs_l2_prefetch_asize' => 'L2 Prefetch Asize',
        'zfs_l2_rb_asize' => 'L2 Rebuild Asize',
        'zfs_l2_abort_lowmem' => 'L2 Abort Lowmem Per Second',
    ];
} else {
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
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = LibreNMS\Config::get('time.now');
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
