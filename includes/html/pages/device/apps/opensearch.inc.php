<?php

$suricata_instances = get_suricata_instances($device['device_id']);

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'opensearch',
];

print_optionbar_start();
echo 'Cluster Name: ' . get_opensearch_cluster_name($device['device_id']) .'<br>';
echo generate_link('Cluster, ', $link_array);
$link_array['set']='translog';
echo generate_link('Translog, ', $link_array);
print_optionbar_end();

if (isset($vars['set'])) {
    $graph_set=$vars['set'];
} else {
    $graph_set=$vars['cluster'];
}

if ($graph_set == 'cluster') {
    $graphs = [
        'opensearch_c_nodes'=>'Nodes',
        'opensearch_c_data_nodes'=>'Data Nodes',
        'opensearch_c_act_pri_shards'=>'Active Primary Shards',
        'opensearch_c_act_shards'=>'Active Shards',
        'opensearch_c_rel_shards'=>'Relocating Shards',
        'opensearch_c_init_shards'=>'Initializing Shards',
        'opensearch_c_delayed_shards'=>'Delayed Shards',
        'opensearch_c_pending_tasks'=>'Pending Tasks',
        'opensearch_c_in_fl_fetch'=>'In Flight Fetches',
        'opensearch_c_task_max_in_time'=>'Tasks Max Time In Milliseconds',
        'opensearch_c_act_shards_perc'=>'Active Shards Percentage',
        'opensearch_status'=>'Status: 0=Green, 1=Yellow, 2=Red, 3=Unknown',
    ];
} elseif ($graph_set == 'translog') {
    $graphs = [
        'opensearch_ttl_ops' => 'Translog Operations',
        'opensearch_ttl_size' => 'Translog Size In Bytes',
        'opensearch_ttl_uncom_ops' => 'Translog Uncommitted Operations',
        'opensearch_ttl_uncom_size' => 'Translog Uncommitted Size In Bytes',
        'opensearch_ttl_last_mod_age' => 'Translog Earliest Last Modified Age',
    ];
} else {
    $graphs = [
        'opensearch_c_nodes'=>'Nodes',
        'opensearch_c_data_nodes'=>'Data Nodes',
        'opensearch_c_act_pri_shards'=>'Active Primary Shards',
        'opensearch_c_act_shards'=>'Active Shards',
        'opensearch_c_rel_shards'=>'Relocating Shards',
        'opensearch_c_init_shards'=>'Initializing Shards',
        'opensearch_c_delayed_shards'=>'Delayed Shards',
        'opensearch_c_pending_tasks'=>'Pending Tasks',
        'opensearch_c_in_fl_fetch'=>'In Flight Fetches',
        'opensearch_c_task_max_in_time'=>'Tasks Max Time In Milliseconds',
        'opensearch_c_act_shards_perc'=>'Active Shards Percentage',
        'opensearch_status'=>'Status: 0=Green, 1=Yellow, 2=Red, 3=Unknown',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
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
