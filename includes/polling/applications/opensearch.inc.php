<?php

use App\Models\Eventlog;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'opensearch';

try {
    $returned = json_app_get($device, 'opensearch');
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$data = $returned['data'];

$rrd_name = ['app', $name, $app->app_id, 'c'];
$rrd_def = RrdDefinition::make()
    ->addDataset('c_nodes', 'GAUGE', 0)
    ->addDataset('c_data_nodes', 'GAUGE', 0)
    ->addDataset('c_act_pri_shards', 'GAUGE', 0)
    ->addDataset('c_act_shards', 'GAUGE', 0)
    ->addDataset('c_rel_shards', 'GAUGE', 0)
    ->addDataset('c_init_shards', 'GAUGE', 0)
    ->addDataset('c_delayed_shards', 'GAUGE', 0)
    ->addDataset('c_unass_shards', 'GAUGE', 0)
    ->addDataset('c_pending_tasks', 'GAUGE', 0)
    ->addDataset('c_in_fl_fetch', 'GAUGE', 0)
    ->addDataset('c_task_max_in_time', 'GAUGE', 0)
    ->addDataset('c_act_shards_perc', 'GAUGE', 0)
    ->addDataset('status', 'GAUGE', 0);
$metrics = [
    'c_nodes' => $data['c_nodes'],
    'c_data_nodes' => $data['c_data_nodes'] ?? null,
    'c_act_pri_shards' => $data['c_act_pri_shards'] ?? null,
    'c_act_shards' => $data['c_act_shards'] ?? null,
    'c_rel_shards' => $data['c_rel_shards'] ?? null,
    'c_init_shards' => $data['c_init_shards'] ?? null,
    'c_delayed_shards' => $data['c_delayed_shards'] ?? null,
    'c_unass_shards' => $data['c_unass_shards'] ?? null,
    'c_pending_tasks' => $data['c_pending_tasks'] ?? null,
    'c_in_fl_fetch' => $data['c_in_fl_fetch'] ?? null,
    'c_task_max_in_time' => $data['c_task_max_in_time'] ?? null,
    'c_act_shards_perc' => $data['c_act_shards_perc'] ?? null,
    'status' => $data['status'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'ttl'];
$rrd_def = RrdDefinition::make()
    ->addDataset('ttl_ops', 'DERIVE', 0)
    ->addDataset('ttl_size', 'GAUGE', 0)
    ->addDataset('ttl_uncom_ops', 'GAUGE', 0)
    ->addDataset('ttl_uncom_size', 'GAUGE', 0)
    ->addDataset('ttl_last_mod_age', 'GAUGE', 0);
$metrics = [
    'ttl_ops' => $data['ttl_ops'] ?? null,
    'ttl_size' => $data['ttl_size'] ?? null,
    'ttl_uncom_ops' => $data['ttl_uncom_ops'] ?? null,
    'ttl_uncom_size' => $data['ttl_uncom_size'] ?? null,
    'ttl_last_mod_age' => $data['ttl_last_mod_age'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'ti'] ?? null;
$rrd_def = RrdDefinition::make()
    ->addDataset('ti_total', 'DERIVE', 0)
    ->addDataset('ti_time', 'DERIVE', 0)
    ->addDataset('ti_failed', 'DERIVE', 0)
    ->addDataset('ti_del_total', 'DERIVE', 0)
    ->addDataset('ti_del_time', 'DERIVE', 0)
    ->addDataset('ti_noop_up_total', 'DERIVE', 0)
    ->addDataset('ti_throttled_time', 'DERIVE', 0)
    ->addDataset('ti_throttled', 'GAUGE', 0);
$metrics = [
    'ti_total' => $data['ti_total'] ?? null,
    'ti_time' => $data['ti_time'] ?? null,
    'ti_failed' => $data['ti_failed'] ?? null,
    'ti_del_total' => $data['ti_del_total'] ?? null,
    'ti_del_time' => $data['ti_del_time'] ?? null,
    'ti_noop_up_total' => $data['ti_noop_up_total'] ?? null,
    'ti_throttled_time' => $data['ti_throttled_time'] ?? null,
    'ti_throttled' => $data['ti_throttled'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'ts'];
$rrd_def = RrdDefinition::make()
    ->addDataset('ts_q_total', 'DERIVE', 0)
    ->addDataset('ts_q_time', 'DERIVE', 0)
    ->addDataset('ts_f_total', 'DERIVE', 0)
    ->addDataset('ts_f_time', 'DERIVE', 0)
    ->addDataset('ts_sc_total', 'DERIVE', 0)
    ->addDataset('ts_sc_time', 'DERIVE', 0)
    ->addDataset('ts_su_total', 'DERIVE', 0)
    ->addDataset('ts_su_time', 'DERIVE', 0);
$metrics = [
    'ts_q_total' => $data['ts_q_total'] ?? null,
    'ts_q_time' => $data['ts_q_time'] ?? null,
    'ts_f_total' => $data['ts_f_total'] ?? null,
    'ts_f_time' => $data['ts_f_time'] ?? null,
    'ts_sc_total' => $data['ts_sc_total'] ?? null,
    'ts_sc_time' => $data['ts_sc_time'] ?? null,
    'ts_su_total' => $data['ts_su_total'] ?? null,
    'ts_su_time' => $data['ts_su_time'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tr'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tr_total', 'DERIVE', 0)
    ->addDataset('tr_time', 'DERIVE', 0)
    ->addDataset('tr_ext_total', 'DERIVE', 0)
    ->addDataset('tr_ext_time', 'DERIVE', 0);
$metrics = [
    'tr_total' => $data['tr_total'] ?? null,
    'tr_time' => $data['tr_time'] ?? null,
    'tr_ext_total' => $data['tr_ext_tota ?? nulll'],
    'tr_ext_time' => $data['tr_ext_time'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tf'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tf_total', 'DERIVE', 0)
    ->addDataset('tf_periodic', 'DERIVE', 0)
    ->addDataset('tf_time', 'DERIVE', 0);
$metrics = [
    'tf_total' => $data['tf_total'] ?? null,
    'tf_periodic' => $data['tf_periodic'] ?? null,
    'tf_time' => $data['tf_time'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tqc'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tqc_size', 'GAUGE', 0)
    ->addDataset('tqc_total', 'GAUGE', 0)
    ->addDataset('tqc_hit', 'DERIVE', 0)
    ->addDataset('tqc_miss', 'DERIVE', 0)
    ->addDataset('tqc_cache_size', 'GAUGE', 0)
    ->addDataset('tqc_cache_count', 'GAUGE', 0)
    ->addDataset('tqc_evictions', 'DERIVE', 0);
$metrics = [
    'tqc_size' => $data['tqc_size'] ?? null,
    'tqc_total' => $data['tqc_total'] ?? null,
    'tqc_hit' => $data['tqc_hit'] ?? null,
    'tqc_miss' => $data['tqc_miss'] ?? null,
    'tqc_cache_size' => $data['tqc_cache_size'] ?? null,
    'tqc_cache_count' => $data['tqc_cache_count'] ?? null,
    'tqc_evictions' => $data['tqc_evictions'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tg'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tg_total', 'DERIVE', 0)
    ->addDataset('tg_time', 'DERIVE', 0)
    ->addDataset('tg_exists_total', 'DERIVE', 0)
    ->addDataset('tg_exists_time', 'DERIVE', 0)
    ->addDataset('tg_missing_total', 'DERIVE', 0)
    ->addDataset('tg_missing_time', 'DERIVE', 0);
$metrics = [
    'tg_total' => $data['tg_total'] ?? null,
    'tg_time' => $data['tg_time'] ?? null,
    'tg_exists_total' => $data['tg_exists_total'] ?? null,
    'tg_exists_time' => $data['tg_exists_time'] ?? null,
    'tg_missing_total' => $data['tg_missing_total'] ?? null,
    'tg_missing_time' => $data['tg_missing_time'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tm'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tm_total', 'DERIVE', 0)
    ->addDataset('tm_time', 'DERIVE', 0)
    ->addDataset('tm_docs', 'DERIVE', 0)
    ->addDataset('tm_size', 'DERIVE', 0)
    ->addDataset('tm_throttled_time', 'DERIVE', 0)
    ->addDataset('tm_throttled_size', 'DERIVE', 0);
$metrics = [
    'tm_total' => $data['tm_total'] ?? null,
    'tm_time' => $data['tm_time'] ?? null,
    'tm_docs' => $data['tm_docs'] ?? null,
    'tm_size' => $data['tm_size'] ?? null,
    'tm_throttled_time' => $data['tm_throttled_time'] ?? null,
    'tm_throttled_size' => $data['tm_throttled_size'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tw'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tw_total', 'DERIVE', 0)
    ->addDataset('tw_time', 'DERIVE', 0);
$metrics = [
    'tw_total' => $data['tw_total'] ?? null,
    'tw_time' => $data['tw_time'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tfd'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tfd_size', 'GAUGE', 0)
    ->addDataset('tfd_evictions', 'DERIVE', 0);
$metrics = [
    'tfd_size' => $data['tfd_size'] ?? null,
    'tfd_evictions' => $data['tfd_evictions'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tseg'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tseg_count', 'GAUGE', 0)
    ->addDataset('tseg_size', 'GAUGE', 0)
    ->addDataset('tseg_terms_size', 'GAUGE', 0)
    ->addDataset('tseg_fields_size', 'GAUGE', 0)
    ->addDataset('tseg_tvector_size', 'GAUGE', 0)
    ->addDataset('tseg_norms_size', 'GAUGE', 0)
    ->addDataset('tseg_points_size', 'GAUGE', 0)
    ->addDataset('tseg_docval_size', 'GAUGE', 0)
    ->addDataset('tseg_indwrt_size', 'GAUGE', 0)
    ->addDataset('tseg_vermap_size', 'GAUGE', 0)
    ->addDataset('tseg_fbs_size', 'GAUGE', 0);
$metrics = [
    'tseg_count' => $data['tseg_count'] ?? null,
    'tseg_size' => $data['tseg_size'] ?? null,
    'tseg_terms_size' => $data['tseg_terms_size'] ?? null,
    'tseg_fields_size' => $data['tseg_fields_size'] ?? null,
    'tseg_tvector_size' => $data['tseg_tvector_size'] ?? null,
    'tseg_norms_size' => $data['tseg_norms_size'] ?? null,
    'tseg_points_size' => $data['tseg_points_size'] ?? null,
    'tseg_docval_size' => $data['tseg_docval_size'] ?? null,
    'tseg_indwrt_size' => $data['tseg_indwrt_size'] ?? null,
    'tseg_vermap_size' => $data['tseg_vermap_size'] ?? null,
    'tseg_fbs_size' => $data['tseg_fbs_size'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'trc'];
$rrd_def = RrdDefinition::make()
    ->addDataset('trc_size', 'GAUGE', 0)
    ->addDataset('trc_evictions', 'DERIVE', 0)
    ->addDataset('trc_hits', 'DERIVE', 0)
    ->addDataset('trc_misses', 'DERIVE', 0);
$metrics = [
    'trc_size' => $data['trc_size'] ?? null,
    'trc_evictions' => $data['trc_evictions'] ?? null,
    'trc_hits' => $data['trc_hits'] ?? null,
    'trc_misses' => $data['trc_misses'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

$rrd_name = ['app', $name, $app->app_id, 'tst'];
$rrd_def = RrdDefinition::make()
    ->addDataset('tst_size', 'GAUGE', 0)
    ->addDataset('tst_res_size', 'GAUGE', 0);
$metrics = [
    'tst_size' => $data['tst_size'] ?? null,
    'tst_res_size' => $data['tst_res_size'] ?? null,
];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $metrics);

// save clustername upon changes and log it post initial set
if (isset($app->data['cluster'])) {
    if ($app->data['cluster'] != $returned['data']['cluster_name']) {
        Eventlog::log('Elastic/Opensearch: Cluster name changed to "' . $returned['data']['cluster_name'] . '"', $device['device_id'], 'application');

        // save the found cluster name
        $app->data = ['cluster' => $returned['data']['cluster_name']];
    }
} else {
    $app->data = ['cluster' => $returned['data']['cluster_name']];
}

//
// update the app metrics
//
unset($returned['data']['cluster_name']);
update_application($app, 'OK', $returned['data']);
