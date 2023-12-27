<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'opensearch',
];

print_optionbar_start();
echo '<b>Cluster Name:</b> ' . $app->data['cluster'] . '<br>';
echo '<b>Graph Sets:</b> ';
echo generate_link('Cluster, ', $link_array);
$link_array['set'] = 'translog';
echo generate_link('Translog, ', $link_array);
$link_array['set'] = 'indexing';
echo generate_link('Indexing, ', $link_array);
$link_array['set'] = 'search';
echo generate_link('Search, ', $link_array);
$link_array['set'] = 'refresh';
echo generate_link('Refresh, ', $link_array);
$link_array['set'] = 'flush';
echo generate_link('Flush, ', $link_array);
$link_array['set'] = 'qc';
echo generate_link('Query_Cache, ', $link_array);
$link_array['set'] = 'get';
echo generate_link('Get, ', $link_array);
$link_array['set'] = 'merges';
echo generate_link('Merges, ', $link_array);
$link_array['set'] = 'warmer';
echo generate_link('Warmer, ', $link_array);
$link_array['set'] = 'fielddata';
echo generate_link('Fielddata, ', $link_array);
$link_array['set'] = 'segments';
echo generate_link('Segments, ', $link_array);
$link_array['set'] = 'rc';
echo generate_link('Request_Cache, ', $link_array);
$link_array['set'] = 'store';
echo generate_link('Store, ', $link_array);
$link_array['set'] = 'time';
echo generate_link('Time, ', $link_array);
$link_array['set'] = 'memory';
echo generate_link('Memory', $link_array);
print_optionbar_end();

if (isset($vars['set'])) {
    $graph_set = $vars['set'];
} else {
    $graph_set = $vars['cluster'];
}

if ($graph_set == 'cluster') {
    $graphs = [
        'opensearch_c_nodes' => 'Nodes',
        'opensearch_c_data_nodes' => 'Data Nodes',
        'opensearch_c_all_shards' => 'Combined Shard Stats',
        'opensearch_c_pending_tasks' => 'Pending Tasks',
        'opensearch_c_in_fl_fetch' => 'In Flight Fetches',
        'opensearch_status' => 'Status: 0=Green, 1=Yellow, 2=Red, 3=Unknown',
        'opensearch_c_task_max_in_time' => 'Tasks Max Time In Milliseconds',
        'opensearch_c_act_shards_perc' => 'Active Shards Percentage',
        'opensearch_c_act_pri_shards' => 'Active Primary Shards',
        'opensearch_c_act_shards' => 'Active Shards',
        'opensearch_c_rel_shards' => 'Relocating Shards',
        'opensearch_c_init_shards' => 'Initializing Shards',
        'opensearch_c_delayed_shards' => 'Delayed Shards',
        'opensearch_c_unass_shards' => 'Unassigned Shards',
    ];
} elseif ($graph_set == 'translog') {
    $graphs = [
        'opensearch_ttl_ops' => 'Translog Operations',
        'opensearch_ttl_size' => 'Translog Size In Bytes',
        'opensearch_ttl_uncom_ops' => 'Translog Uncommitted Operations',
        'opensearch_ttl_uncom_size' => 'Translog Uncommitted Size In Bytes',
        'opensearch_ttl_last_mod_age' => 'Translog Earliest Last Modified Age',
    ];
} elseif ($graph_set == 'indexing') {
    $graphs = [
        'opensearch_ti_total' => 'Index',
        'opensearch_ti_time' => 'Index Time',
        'opensearch_ti_failed' => 'Index Failed',
        'opensearch_ti_del_total' => 'Delete',
        'opensearch_ti_del_time' => 'Delete Time',
        'opensearch_ti_noop_up_total' => 'NoOP Update',
        'opensearch_ti_throttled_time' => 'Throttle Time',
        'opensearch_ti_throttled' => 'Throttled',
    ];
} elseif ($graph_set == 'search') {
    $graphs = [
        'opensearch_ts_q_total' => 'Queries',
        'opensearch_ts_q_time' => 'Query Time',
        'opensearch_ts_f_total' => 'Fetch',
        'opensearch_ts_f_time' => 'Fetch Time',
        'opensearch_ts_sc_total' => 'Scrolls',
        'opensearch_ts_sc_time' => 'Scroll Time',
        'opensearch_ts_su_total' => 'Suggests',
        'opensearch_ts_su_time' => 'Suggest Time',
    ];
} elseif ($graph_set == 'refresh') {
    $graphs = [
        'opensearch_tr_total' => 'Refreshes',
        'opensearch_tr_time' => 'Refresh Time',
        'opensearch_tr_ext_total' => 'External',
        'opensearch_tr_ext_time' => 'External Time',
    ];
} elseif ($graph_set == 'flush') {
    $graphs = [
        'opensearch_tf_total' => 'Flushes',
        'opensearch_tf_periodic' => 'Periodic',
        'opensearch_tf_time' => 'Flush Time',
    ];
} elseif ($graph_set == 'qc') {
    $graphs = [
        'opensearch_tqc_size' => 'Query Cache Memory Size',
        'opensearch_tqc_total' => 'Query Cache',
        'opensearch_tqc_hit' => 'Query Cache Hits',
        'opensearch_tqc_miss' => 'Query Cache Misses',
        'opensearch_tqc_cache_size' => 'Query Cache Size',
        'opensearch_tqc_cache_count' => 'Query Cache Count',
        'opensearch_tqc_evictions' => 'Query Cache Evictions',
    ];
} elseif ($graph_set == 'get') {
    $graphs = [
        'opensearch_tg_total' => 'Gets',
        'opensearch_tg_time' => 'Get Time',
        'opensearch_tg_exists_total' => 'Exists',
        'opensearch_tg_exists_time' => 'Get Exists Time',
        'opensearch_tg_missing_total' => 'Get Missing Total',
        'opensearch_tg_missing_time' => 'Get Missing Time',
    ];
} elseif ($graph_set == 'merges') {
    $graphs = [
        'opensearch_tm_total' => 'Merges',
        'opensearch_tm_time' => 'Merges Time',
        'opensearch_tm_docs' => 'Merges Docs',
        'opensearch_tm_size' => 'Merges Size',
        'opensearch_tm_throttled_time' => 'Merges Throttled Time',
        'opensearch_tm_throttled_size' => 'Merges Throttled Size',
    ];
} elseif ($graph_set == 'warmer') {
    $graphs = [
        'opensearch_tw_total' => 'Warmer',
        'opensearch_tw_time' => 'Warmer Time',
    ];
} elseif ($graph_set == 'fielddata') {
    $graphs = [
        'opensearch_tfd_size' => 'Fielddata Memory Size',
        'opensearch_tfd_evictions' => ' Fielddata Evictions',
    ];
} elseif ($graph_set == 'segments') {
    $graphs = [
        'opensearch_tseg_count' => 'Segments Count',
        'opensearch_tseg_size' => 'Segments Memory Size',
        'opensearch_tseg_terms_size' => 'Segments Terms Memory Size',
        'opensearch_tseg_fields_size' => 'Segments Stored Fields Memory Size',
        'opensearch_tseg_tvector_size' => 'Segments Term Vertors Memory Size',
        'opensearch_tseg_norms_size' => 'Segments Norms Memory Size',
        'opensearch_tseg_points_size' => 'Segments Points Memory Size',
        'opensearch_tseg_docval_size' => 'Segments Doc Values Memory Size',
        'opensearch_tseg_indwrt_size' => 'Segments Index Writer Memory Size',
        'opensearch_tseg_vermap_size' => 'Segments Version Map Memory Size',
        'opensearch_tseg_fbs_size' => 'Segment Fixed Bit Set Memory Size',
    ];
} elseif ($graph_set == 'rc') {
    $graphs = [
        'opensearch_trc_size' => 'Request Cache Memory Size',
        'opensearch_trc_evictions' => 'Request Cache Evictions',
        'opensearch_trc_hits' => 'Request Cache Hits',
        'opensearch_trc_misses' => 'Request Cache Misses',
    ];
} elseif ($graph_set == 'store') {
    $graphs = [
        'opensearch_tst_size' => 'Store Size',
        'opensearch_tst_res_size' => 'Store Reserved Size',
    ];
} elseif ($graph_set == 'time') {
    $graphs = [
        'opensearch_c_task_max_in_time' => 'Tasks Max Time',
        'opensearch_time_all' => 'Combined',
        'opensearch_ti_time' => 'Index Time',
        'opensearch_ti_del_time' => 'Delete Time',
        'opensearch_ti_throttled_time' => 'Throttle Time',
        'opensearch_ts_q_time' => 'Query Time',
        'opensearch_ts_f_time' => 'Fetch Time',
        'opensearch_ts_sc_time' => 'Scroll Time',
        'opensearch_ts_su_time' => 'Suggest Time',
        'opensearch_tr_time' => 'Refresh Time',
        'opensearch_tr_ext_time' => 'External Time',
        'opensearch_tf_time' => 'Flush Time',
        'opensearch_tg_time' => 'Get Time',
        'opensearch_tg_exists_time' => 'Get Exists Time',
        'opensearch_tg_missing_time' => 'Get Missing Time',
        'opensearch_tm_time' => 'Merges Time',
        'opensearch_tm_throttled_time' => 'Merges Throttled Time',
        'opensearch_tw_time' => 'Warmer Time',
    ];
} elseif ($graph_set == 'memory') {
    $graphs = [
        'opensearch_mem_all' => 'Combined',
        'opensearch_tqc_size' => 'Query Cache Size',
        'opensearch_trc_size' => 'Request Cache Memory Size',
        'opensearch_tfd_size' => 'Field Data Memory Size',
        'opensearch_tseg_size' => 'Segments Memory Size',
        'opensearch_tseg_terms_size' => 'Segments Terms Memory Size',
        'opensearch_tseg_fields_size' => 'Segments Stored Fields Memory Size',
        'opensearch_tseg_tvector_size' => 'Segments Term Vertors Memory Size',
        'opensearch_tseg_norms_size' => 'Segments Norms Memory Size',
        'opensearch_tseg_points_size' => 'Segments Points Memory Size',
        'opensearch_tseg_docval_size' => 'Segments Doc Values Memory Size',
        'opensearch_tseg_indwrt_size' => 'Segments Index Writer Memory Size',
        'opensearch_tseg_vermap_size' => 'Segments Version Map Memory Size',
        'opensearch_tseg_fbs_size' => 'Segment Fixed Bit Set Memory Size',
    ];
} else {
    $graphs = [
        'opensearch_c_nodes' => 'Nodes',
        'opensearch_c_data_nodes' => 'Data Nodes',
        'opensearch_c_all_shards' => 'Combined Shard Stats',
        'opensearch_c_pending_tasks' => 'Pending Tasks',
        'opensearch_c_in_fl_fetch' => 'In Flight Fetches',
        'opensearch_status' => 'Status: 0=Green, 1=Yellow, 2=Red, 3=Unknown',
        'opensearch_c_task_max_in_time' => 'Tasks Max Time In Milliseconds',
        'opensearch_c_act_shards_perc' => 'Active Shards Percentage',
        'opensearch_c_act_pri_shards' => 'Active Primary Shards',
        'opensearch_c_act_shards' => 'Active Shards',
        'opensearch_c_rel_shards' => 'Relocating Shards',
        'opensearch_c_init_shards' => 'Initializing Shards',
        'opensearch_c_delayed_shards' => 'Delayed Shards',
        'opensearch_c_unass_shards' => 'Unassigned Shards',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app->app_id;
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
