<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'zfs';
// Is set to false later if missing keys are found.
$not_legacy = 1;

try {
    $all_return = json_app_get($device, $name, 1);
    $zfs = $all_return['data'];
} catch (JsonAppMissingKeysException $e) {
    //old version with out the data key
    $zfs = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('deleted', 'DERIVE', 0)
    ->addDataset('evict_skip', 'DERIVE', 0)
    ->addDataset('mutex_skip', 'DERIVE', 0)
    ->addDataset('recycle_miss', 'DERIVE', 0)
    ->addDataset('arc_size', 'GAUGE', 0)
    ->addDataset('target_size_max', 'GAUGE', 0)
    ->addDataset('target_size_min', 'GAUGE', 0)
    ->addDataset('target_size', 'GAUGE', 0)
    ->addDataset('target_size_per', 'GAUGE', 0)
    ->addDataset('arc_size_per', 'GAUGE', 0)
    ->addDataset('target_size_arat', 'GAUGE', 0)
    ->addDataset('min_size_per', 'GAUGE', 0)
    ->addDataset('mfu_size', 'GAUGE', 0)
    ->addDataset('p', 'GAUGE', 0)
    ->addDataset('rec_used_per', 'GAUGE', 0)
    ->addDataset('freq_used_per', 'GAUGE', 0)
    ->addDataset('arc_hits', 'DERIVE', 0)
    ->addDataset('arc_misses', 'DERIVE', 0)
    ->addDataset('demand_data_hits', 'DERIVE', 0)
    ->addDataset('demand_data_misses', 'DERIVE', 0)
    ->addDataset('demand_meta_hits', 'DERIVE', 0)
    ->addDataset('demand_meta_misses', 'DERIVE', 0)
    ->addDataset('mfu_ghost_hits', 'DERIVE', 0)
    ->addDataset('mfu_hits', 'DERIVE', 0)
    ->addDataset('mru_ghost_hits', 'DERIVE', 0)
    ->addDataset('mru_hits', 'DERIVE', 0)
    ->addDataset('pre_data_hits', 'DERIVE', 0)
    ->addDataset('pre_data_misses', 'DERIVE', 0)
    ->addDataset('pre_meta_hits', 'DERIVE', 0)
    ->addDataset('pre_meta_misses', 'DERIVE', 0)
    ->addDataset('anon_hits', 'DERIVE', 0)
    ->addDataset('arc_accesses_total', 'DERIVE', 0)
    ->addDataset('demand_data_total', 'DERIVE', 0)
    ->addDataset('pre_data_total', 'DERIVE', 0)
    ->addDataset('real_hits', 'DERIVE', 0)
    ->addDataset('cache_hits_per', 'GAUGE', 0)
    ->addDataset('cache_miss_per', 'GAUGE', 0)
    ->addDataset('actual_hit_per', 'GAUGE', 0)
    ->addDataset('data_demand_per', 'GAUGE', 0)
    ->addDataset('data_pre_per', 'GAUGE', 0)
    ->addDataset('anon_hits_per', 'GAUGE', 0)
    ->addDataset('mru_per', 'GAUGE', 0)
    ->addDataset('mfu_per', 'GAUGE', 0)
    ->addDataset('mru_ghost_per', 'GAUGE', 0)
    ->addDataset('mfu_ghost_per', 'GAUGE', 0)
    ->addDataset('demand_hits_per', 'GAUGE', 0)
    ->addDataset('pre_hits_per', 'GAUGE', 0)
    ->addDataset('meta_hits_per', 'GAUGE', 0)
    ->addDataset('pre_meta_hits_per', 'GAUGE', 0)
    ->addDataset('demand_misses_per', 'GAUGE', 0)
    ->addDataset('pre_misses_per', 'GAUGE', 0)
    ->addDataset('meta_misses_per', 'GAUGE', 0)
    ->addDataset('pre_meta_misses_per', 'GAUGE', 0);

$fields = [
    'deleted' => $zfs['deleted'],
    'evict_skip' => $zfs['evict_skip'],
    'mutex_skip' => $zfs['mutex_skip'],
    'recycle_miss' => $zfs['recycle_miss'],
    'arc_size' => $zfs['arc_size'],
    'target_size_max' => $zfs['target_size_max'],
    'target_size_min' => $zfs['target_size_min'],
    'target_size' => $zfs['target_size'],
    'target_size_per' => $zfs['target_size_per'],
    'arc_size_per' => $zfs['arc_size_per'],
    'target_size_arat' => $zfs['target_size_arat'],
    'min_size_per' => $zfs['min_size_per'],
    'mfu_size' => $zfs['mfu_size'],
    'p' => $zfs['p'],
    'rec_used_per' => $zfs['rec_used_per'],
    'freq_used_per' => $zfs['freq_used_per'],
    'arc_hits' => $zfs['arc_hits'],
    'arc_misses' => $zfs['arc_misses'],
    'demand_data_hits' => $zfs['demand_data_hits'],
    'demand_data_misses' => $zfs['demand_data_misses'],
    'demand_meta_hits' => $zfs['demand_meta_hits'],
    'demand_meta_misses' => $zfs['demand_meta_misses'],
    'mfu_ghost_hits' => $zfs['mfu_ghost_hits'],
    'mfu_hits' => $zfs['mfu_hits'],
    'mru_ghost_hits' => $zfs['mru_ghost_hits'],
    'mru_hits' => $zfs['mru_hits'],
    'pre_data_hits' => $zfs['pre_data_hits'],
    'pre_data_misses' => $zfs['pre_data_misses'],
    'pre_meta_hits' => $zfs['pre_meta_hits'],
    'pre_meta_misses' => $zfs['pre_meta_misses'],
    'anon_hits' => $zfs['anon_hits'],
    'arc_accesses_total' => $zfs['arc_accesses_total'],
    'demand_data_total' => $zfs['demand_data_total'],
    'pre_data_total' => $zfs['pre_data_total'],
    'real_hits' => $zfs['real_hits'],
    'cache_hits_per' => $zfs['cache_hits_per'],
    'cache_miss_per' => $zfs['cache_miss_per'],
    'actual_hit_per' => $zfs['actual_hit_per'],
    'data_demand_per' => $zfs['data_demand_per'],
    'data_pre_per' => $zfs['data_pre_per'],
    'anon_hits_per' => $zfs['anon_hits_per'],
    'mru_per' => $zfs['mru_per'],
    'mfu_per' => $zfs['mfu_per'],
    'mru_ghost_per' => $zfs['mru_ghost_per'],
    'mfu_ghost_per' => $zfs['mfu_ghost_per'],
    'demand_hits_per' => $zfs['demand_hits_per'],
    'pre_hits_per' => $zfs['pre_hits_per'],
    'meta_hits_per' => $zfs['meta_hits_per'],
    'pre_meta_hits_per' => $zfs['pre_meta_hits_per'],
    'demand_misses_per' => $zfs['demand_misses_per'],
    'pre_misses_per' => $zfs['pre_misses_per'],
    'meta_misses_per' => $zfs['meta_misses_per'],
    'pre_meta_misses_per' => $zfs['pre_meta_misses_per'],
];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

// name choosen based on this is the second group of variables
$rrd_name = ['app', $name, $app->app_id, '_____group2'];
$rrd_def = RrdDefinition::make()
    ->addDataset('l2_abort_lowmem', 'DERIVE', 0)
    ->addDataset('l2_access_total', 'DERIVE', 0)
    ->addDataset('l2_asize', 'GAUGE', 0)
    ->addDataset('l2_bufc_d_asize', 'GAUGE', 0)
    ->addDataset('l2_bufc_m_asize', 'GAUGE', 0)
    ->addDataset('l2_cksum_bad', 'DERIVE', 0)
    ->addDataset('l2_d_to_m_ratio', 'GAUGE', 0)
    ->addDataset('l2_errors', 'DERIVE', 0)
    ->addDataset('l2_evict_l1cached', 'DERIVE', 0)
    ->addDataset('l2_evict_l_retry', 'DERIVE', 0)
    ->addDataset('l2_evict_reading', 'DERIVE', 0)
    ->addDataset('l2_feeds', 'DERIVE', 0)
    ->addDataset('l2_free_on_write', 'DERIVE', 0)
    ->addDataset('l2_hdr_size', 'GAUGE', 0)
    ->addDataset('l2_hits', 'DERIVE', 0)
    ->addDataset('l2_io_error', 'DERIVE', 0)
    ->addDataset('l2_log_blk_asize', 'GAUGE', 0)
    ->addDataset('l2_log_blk_avg_as', 'DERIVE', 0)
    ->addDataset('l2_log_blk_count', 'DERIVE', 0)
    ->addDataset('l2_log_blk_writes', 'DERIVE', 0)
    ->addDataset('l2_mfu_asize', 'GAUGE', 0)
    ->addDataset('l2_misses', 'DERIVE', 0)
    ->addDataset('l2_mru_asize', 'GAUGE', 0)
    ->addDataset('l2_prefetch_asize', 'GAUGE', 0)
    ->addDataset('l2_read_bytes', 'DERIVE', 0)
    ->addDataset('l2_rb_asize', 'GAUGE', 0)
    ->addDataset('l2_rb_bufs', 'DERIVE', 0)
    ->addDataset('l2_rb_bufs_prec', 'DERIVE', 0)
    ->addDataset('l2_rb_csum_lb_err', 'DERIVE', 0)
    ->addDataset('l2_rb_dh_err', 'DERIVE', 0)
    ->addDataset('l2_rb_io_errors', 'DERIVE', 0)
    ->addDataset('l2_rb_log_blks', 'DERIVE', 0)
    ->addDataset('l2_rb_lowmem', 'DERIVE', 0)
    ->addDataset('l2_rb_size', 'GAUGE', 0)
    ->addDataset('l2_rb_success', 'DERIVE', 0)
    ->addDataset('l2_rb_unsup', 'DERIVE', 0)
    ->addDataset('l2_rw_clash', 'DERIVE', 0)
    ->addDataset('l2_size', 'GAUGE', 0)
    ->addDataset('l2_write_bytes', 'DERIVE', 0)
    ->addDataset('l2_writes_done', 'DERIVE', 0)
    ->addDataset('l2_writes_error', 'DERIVE', 0)
    ->addDataset('l2_writes_l_retry', 'DERIVE', 0)
    ->addDataset('l2_writes_sent', 'DERIVE', 0);

$fields = [
    'l2_abort_lowmem' => $zfs['l2_abort_lowmem'],
    'l2_access_total' => $zfs['l2_access_total'],
    'l2_asize' => $zfs['l2_asize'],
    'l2_bufc_d_asize' => $zfs['l2_bufc_data_asize'],
    'l2_bufc_m_asize' => $zfs['l2_bufc_metadata_asize'],
    'l2_cksum_bad' => $zfs['l2_cksum_bad'],
    'l2_d_to_m_ratio' => $zfs['l2_data_to_meta_ratio'],
    'l2_errors' => $zfs['l2_errors'],
    'l2_evict_l1cached' => $zfs['l2_evict_l1cached'],
    'l2_evict_l_retry' => $zfs['l2_evict_lock_retry'],
    'l2_evict_reading' => $zfs['l2_evict_reading'],
    'l2_feeds' => $zfs['l2_feeds'],
    'l2_free_on_write' => $zfs['l2_free_on_write'],
    'l2_hdr_size' => $zfs['l2_hdr_size'],
    'l2_hits' => $zfs['l2_hits'],
    'l2_io_error' => $zfs['l2_io_error'],
    'l2_log_blk_asize' => $zfs['l2_log_blk_asize'],
    'l2_log_blk_avg_as' => $zfs['l2_log_blk_avg_asize'],
    'l2_log_blk_count' => $zfs['l2_log_blk_count'],
    'l2_log_blk_writes' => $zfs['l2_log_blk_writes'],
    'l2_mfu_asize' => $zfs['l2_mfu_asize'],
    'l2_misses' => $zfs['l2_misses'],
    'l2_mru_asize' => $zfs['l2_mru_asize'],
    'l2_prefetch_asize' => $zfs['l2_prefetch_asize'],
    'l2_read_bytes' => $zfs['l2_read_bytes'],
    'l2_rb_asize' => $zfs['l2_rebuild_asize'],
    'l2_rb_bufs' => $zfs['l2_rebuild_bufs'],
    'l2_rb_bufs_prec' => $zfs['l2_rebuild_bufs_precached'],
    'l2_rb_csum_lb_err' => $zfs['l2_rebuild_cksum_lb_errors'],
    'l2_rb_dh_err' => $zfs['l2_rebuild_dh_errors'],
    'l2_rb_io_errors' => $zfs['l2_rebuild_io_errors'],
    'l2_rb_log_blks' => $zfs['l2_rebuild_log_blks'],
    'l2_rb_lowmem' => $zfs['l2_rebuild_lowmem'],
    'l2_rb_size' => $zfs['l2_rebuild_size'],
    'l2_rb_success' => $zfs['l2_rebuild_success'],
    'l2_rb_unsup' => $zfs['l2_rebuild_unsupported'],
    'l2_rw_clash' => $zfs['l2_rw_clash'],
    'l2_size' => $zfs['l2_size'],
    'l2_write_bytes' => $zfs['l2_write_bytes'],
    'l2_writes_done' => $zfs['l2_writes_done'],
    'l2_writes_error' => $zfs['l2_writes_error'],
    'l2_writes_l_retry' => $zfs['l2_writes_lock_retry'],
    'l2_writes_sent' => $zfs['l2_writes_sent'],
];

$rrd_def_gauge = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

$gauges_to_check_for = [
    'asyncq_read_a',
    'asyncq_read_p',
    'asyncq_wait_r',
    'asyncq_wait_w',
    'asyncq_write_a',
    'asyncq_write_p',
    'bandwidth_r',
    'bandwidth_w',
    'disk_wait_r',
    'disk_wait_w',
    'operations_r',
    'operations_w',
    'read_errors',
    'checksum_errors',
    'write_errors',
    'scrub_wait',
    'scrubq_read_a',
    'scrubq_read_p',
    'syncq_read_a',
    'syncq_read_p',
    'syncq_wait_r',
    'syncq_wait_w',
    'syncq_write_a',
    'syncq_write_p',
    'total_wait_r',
    'total_wait_w',
    'trim_wait',
    'trimq_write_a',
    'trimq_write_p',
];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// process additional info returned
//

$pools = [];
$pool_rrd_def = RrdDefinition::make()
    ->addDataset('size', 'GAUGE', 0)
    ->addDataset('alloc', 'GAUGE', 0)
    ->addDataset('free', 'GAUGE', 0)
    ->addDataset('expandsz', 'GAUGE', 0)
    ->addDataset('frag', 'GAUGE', 0)
    ->addDataset('cap', 'GAUGE', 0)
    ->addDataset('dedup', 'GAUGE', 0);

$metrics = $zfs; // copy $zfs data to $metrics
unset($metrics['pools']); // remove pools it is an array, re-add data below

$zpool_status = $app->data['status_info'] ?? [];
foreach ($zfs['pools'] as $pool) {
    $pools[] = $pool['name'];
    $rrd_name = ['app', $name, $app->app_id, $pool['name']];
    $fields = [
        'alloc' => $pool['alloc'],
        'size' => $pool['size'],
        'free' => $pool['free'],
        'expandsz' => $pool['expandsz'],
        'frag' => set_numeric($pool['frag'], -1),
        'cap' => $pool['cap'],
        'dedup' => $pool['dedup'],
    ];

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $pool_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    // insert flattened pool metrics into the metrics array
    foreach ($fields as $field => $value) {
        $metrics['pool_' . $pool['name'] . '_' . $field] = $value;
    }

    // process new guage stuff for pools
    foreach ($gauges_to_check_for as $gauge_var) {
        if (isset($pool[$gauge_var])) {
            $metrics['pool_' . $pool['name'] . '_' . $gauge_var] = $pool[$gauge_var];
            $rrd_name = ['app', $name, $app->app_id, $pool['name'] . '____' . $gauge_var];
            $fields = [
                'data' => $pool[$gauge_var],
            ];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def_gauge, 'rrd_name' => $rrd_name];
            data_update($device, 'app', $tags, $fields);
        }
    }

    // save the status if it exists
    if (isset($pool['status'])) {
        $zpool_status[$pool['name']] = $pool['status'];
    }
}

// gets the pool health status
$old_health = $app->data['health'] ?? 1;
if (isset($zfs['health'])) {
    $health = $zfs['health'];
    if ($old_health != $zfs['health']) {
        if ($zfs['health'] == 1) {
            log_event('ZFS pool(s) now healthy', $device, 'application', 1);
        } else {
            log_event('ZFS pool(s) DEGRADED, FAULTED, UNAVAIL, REMOVED, or unknown', $device, 'application', 5);
        }
    }
} else {
    $health = 1;
}

// gets the l2 error status
$old_l2_errors = $app->data['l2_errors'] ?? 0;
if (isset($zfs['l2_errors'])) {
    if ($old_l2_errors != $zfs['l2_errors']) {
        log_event('ZFS L2 cache has experienced errors', $device, 'application', 5);
    }
}

// check for added or removed pools
$old_pools = $app->data['pools'] ?? [];
$added_pools = array_diff($pools, $old_pools);
$removed_pools = array_diff($old_pools, $pools);

// if we have any pool changes log it
if (count($added_pools) > 0 || count($removed_pools) > 0) {
    $log_message = 'ZFS Pool Change:';
    $log_message .= count($added_pools) > 0 ? ' Added ' . implode(',', $added_pools) : '';
    $log_message .= count($removed_pools) > 0 ? ' Removed ' . implode(',', $added_pools) : '';
    log_event($log_message, $device, 'application');
}

// update the app data
$app->data = [
    'pools' => $pools,
    'health' => $health,
    'version' => $all_return['version'],
    'l2_errors' => $zfs['l2_errors'],
    'status_info' => $zpool_status,
];

update_application($app, 'OK', $metrics);
