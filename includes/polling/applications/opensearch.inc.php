<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'opensearch';
$app_id = $app['app_id'];

try {
    $returned = json_app_get($device, 'opensearch');
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$data = $returned['data'];

// grab  the alert here as it is the global one
$metrics = [
    'c_nodes' => $data['c_nodes'],
    'c_data_nodes' => $data['c_data_nodes'],
    'c_act_pri_shards' => $data['c_act_pri_shards'],
    'c_act_shards' => $data['c_act_shards'],
    'c_rel_shards' => $data['c_rel_shards'],
    'c_init_shards' => $data['c_init_shards'],
    'c_delayed_shards' => $data['c_delayed_shards'],
    'c_pending_tasks' => $data['c_pending_tasks'],
    'c_in_fl_fetch' => $data['c_in_fl_fetch'],
    'c_task_max_in_time' => $data['c_task_max_in_time'],
    'c_act_shards_perc' => $data['c_act_shards_perc'],
    'status' => $data['status'],
    'ttl_ops' => $data['ttl_ops'],
    'ttl_size' => $data['ttl_size'],
    'ttl_uncom_ops' => $data['ttl_uncom_ops'],
    'ttl_uncom_size' => $data['ttl_uncom_size'],
    'ttl_last_mod_age' => $data['ttl_last_mod_age'],
    'ti_total' => $data['ti_total'],
    'ti_time' => $data['ti_time'],
    'ti_failed' => $data['ti_failed'],
    'ti_del_total' => $data['ti_del_total'],
    'ti_del_time' => $data['ti_del_time'],
    'ti_noop_up_total' => $data['ti_noop_up_total'],
    'ti_throttled_time' => $data['ti_throttled_time'],
    'ti_throttled' => $data['ti_throttled'],
    'ts_q_total' => $data['ts_q_total'],
    'ts_q_time' => $data['ts_q_time'],
    'ts_f_total' => $data['ts_f_total'],
    'ts_f_time' => $data['ts_f_time'],
    'ts_sc_total' => $data['ts_sc_total'],
    'ts_sc_time' => $data['ts_sc_time'],
    'ts_su_total' => $data['ts_su_total'],
    'ts_su_time' => $data['ts_su_time'],
    'tr_total' => $data['tr_total'],
    'tr_time' => $data['tr_time'],
    'tr_ext_total' => $data['tr_ext_total'],
    'tr_ext_time' => $data['tr_ext_time'],
    'tf_total' => $data['tf_total'],
    'tf_periodic' => $data['tf_periodic'],
    'tf_time' => $data['tf_time'],
    'tqc_size' => $data['tqc_size'],
    'tqc_total' => $data['tqc_total'],
    'tqc_hit' => $data['tqc_hit'],
    'tqc_miss' => $data['tqc_miss'],
    'tqc_cache_size' => $data['tqc_cache_size'],
    'tqc_cache_count' => $data['tqc_cache_count'],
    'tqc_evictions' => $data['tqc_evictions'],
    'tg_total' => $data['tg_total'],
    'tg_time' => $data['tg_time'],
    'tg_exists_total' => $data['tg_exists_total'],
    'tg_exists_time' => $data['tg_exists_time'],
    'tg_missing_total' => $data['tg_missing_total'],
    'tg_missing_time' => $data['tg_missing_time'],
    'tm_total' => $data['tm_total'],
    'tm_time' => $data['tm_time'],
    'tm_docs' => $data['tm_docs'],
    'tm_size' => $data['tm_size'],
    'tm_throttled_time' => $data['tm_throttled_time'],
    'tm_throttled_size' => $data['tm_throttled_size'],
    'tw_total' => $data['tw_total'],
    'tfd_size' => $data['tfd_size'],
    'tfd_evictions' => $data['tfd_evictions'],
    'tseg_count' => $data['tseg_count'],
    'tseg_size' => $data['tseg_size'],
    'tseg_terms_size' => $data['tseg_terms_size'],
    'tseg_fields_size' => $data['tseg_fields_size'],
    'tseg_tvector_size' => $data['tseg_tvector_size'],
    'tseg_norms_size' => $data['tseg_norms_size'],
    'tseg_points_size' => $data['tseg_points_size'],
    'tseg_docval_size' => $data['tseg_docval_size'],
    'tseg_indwrt_size' => $data['tseg_indwrt_size'],
    'tseg_vermap_size' => $data['tseg_vermap_size'],
    'tseg_fbs_size' => $data['tseg_fbs_size'],
    'trc_size' => $data['trc_size'],
    'trc_evictions' => $data['trc_evictions'],
    'trc_hits' => $data['trc_hits'],
    'trc_misses' => $data['trc_misses'],
    'tst_size' => $data['tst_size'],
    'tst_res_size' => $data['tst_res_size']
];

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('c_nodes', 'GAUGE', 0)
    ->addDataset('c_data_nodes', 'GAUGE', 0)
    ->addDataset('c_act_pri_shards', 'GAUGE', 0)
    ->addDataset('c_act_shards', 'GAUGE', 0)
    ->addDataset('c_rel_shards', 'GAUGE', 0)
    ->addDataset('c_init_shards', 'GAUGE', 0)
    ->addDataset('c_delayed_shards', 'GAUGE', 0)
    ->addDataset('c_pending_tasks', 'GAUGE', 0)
    ->addDataset('c_in_fl_fetch', 'GAUGE', 0)
    ->addDataset('c_task_max_in_time', 'GAUGE', 0)
    ->addDataset('c_act_shards_perc', 'GAUGE', 0)
    ->addDataset('status', 'GAUGE', 0)
    ->addDataset('ttl_ops', 'DERIVE', 0)
    ->addDataset('ttl_size', 'GAUGE', 0)
    ->addDataset('ttl_uncom_ops', 'GAUGE', 0)
    ->addDataset('ttl_uncom_size', 'GAUGE', 0)
    ->addDataset('ttl_last_mod_age', 'DERIVE', 0)
    ->addDataset('ti_total', 'DERIVE', 0)
    ->addDataset('ti_time', 'DERIVE', 0)
    ->addDataset('ti_failed', 'DERIVE', 0)
    ->addDataset('ti_del_total', 'DERIVE', 0)
    ->addDataset('ti_del_time', 'DERIVE', 0)
    ->addDataset('ti_noop_up_total', 'DERIVE', 0)
    ->addDataset('ti_throttled_time', 'DERIVE', 0)
    ->addDataset('ti_throttled', 'GAUGE', 0)
    ->addDataset('ts_q_total', 'DERIVE', 0)
    ->addDataset('ts_q_time', 'DERIVE', 0)
    ->addDataset('ts_f_total', 'DERIVE', 0)
    ->addDataset('ts_f_time', 'DERIVE', 0)
    ->addDataset('ts_sc_total', 'DERIVE', 0)
    ->addDataset('ts_sc_time', 'DERIVE', 0)
    ->addDataset('ts_su_total', 'DERIVE', 0)
    ->addDataset('ts_su_time', 'DERIVE', 0)
    ->addDataset('tr_total', 'DERIVE', 0)
    ->addDataset('tr_time', 'DERIVE', 0)
    ->addDataset('tr_ext_total', 'DERIVE', 0)
    ->addDataset('tr_ext_time', 'DERIVE', 0)
    ->addDataset('tf_total', 'DERIVE', 0)
    ->addDataset('tf_periodic', 'DERIVE', 0)
    ->addDataset('tf_time', 'DERIVE', 0)
    ->addDataset('tqc_size', 'GAUGE', 0)
    ->addDataset('tqc_total', 'DERIVE', 0)
    ->addDataset('tqc_hit', 'DERIVE', 0)
    ->addDataset('tqc_miss', 'DERIVE', 0)
    ->addDataset('tqc_cache_size', 'GAUGE', 0)
    ->addDataset('tqc_cache_count', 'GAUGE', 0)
    ->addDataset('tqc_evictions', 'DERIVE', 0)
    ->addDataset('tg_total', 'DERIVE', 0)
    ->addDataset('tg_time', 'DERIVE', 0)
    ->addDataset('tg_exists_total', 'DERIVE', 0)
    ->addDataset('tg_exists_time', 'DERIVE', 0)
    ->addDataset('tg_missing_total', 'DERIVE', 0)
    ->addDataset('tg_missing_time', 'DERIVE', 0)
    ->addDataset('tm_total', 'DERIVE', 0)
    ->addDataset('tm_time', 'DERIVE', 0)
    ->addDataset('tm_docs', 'GAUGE', 0)
    ->addDataset('tm_size', 'GAUGE', 0)
    ->addDataset('tm_throttled_time', 'DERIVE', 0)
    ->addDataset('tm_throttled_size', 'GAUGE', 0)
    ->addDataset('tw_total', 'GAUGE', 0)
    ->addDataset('tfd_size', 'GAUGE', 0)
    ->addDataset('tfd_evictions', 'DERIVE', 0)
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
    ->addDataset('tseg_fbs_size', 'GAUGE', 0)
    ->addDataset('trc_size', 'GAUGE', 0)
    ->addDataset('trc_evictions', 'DERIVE', 0)
    ->addDataset('trc_hits', 'DERIVE', 0)
    ->addDataset('trc_misses', 'DERIVE', 0)
    ->addDataset('tst_size', 'GAUGE', 0)
    ->addDataset('tst_res_size', 'GAUGE', 0);

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $metrics);

//
// mostly done so update the app metrics
//
update_application($app, 'OK', $metrics);

//
// component processing
//
$device_id = $device['device_id'];
$options = [
    'filter' => [
        'device_id' => ['=', $device_id],
        'type' => ['=', $name],
    ],
];

$component = new LibreNMS\Component();
$components = $component->getComponents($device_id, $options);

if (isset($components[$device_id])) {
    $ourc = $components[$device_id];
} else {
    $ourc = $component->createComponent($device_id, $name);
}

$id = $component->getFirstComponentID($ourc);
$ourc[$id]['label'] = 'Opensearch';
$ourc[$id]['cluster'] = $returned['data']['cluster_name'];
$component->setComponentPrefs($device_id, $ourc);
