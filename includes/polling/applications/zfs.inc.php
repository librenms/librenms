<?php

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
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
$rrd_def = RrdDefinition::make();
$fields = [];
$data_sets = [
    'deleted' => 'DERIVE',
    'evict_skip' => 'DERIVE',
    'mutex_skip' => 'DERIVE',
    'recycle_miss' => 'DERIVE',
    'arc_size' => 'GAUGE',
    'target_size_max' => 'GAUGE',
    'target_size_min' => 'GAUGE',
    'target_size' => 'GAUGE',
    'target_size_per' => 'GAUGE',
    'arc_size_per' => 'GAUGE',
    'target_size_arat' => 'GAUGE',
    'min_size_per' => 'GAUGE',
    'mfu_size' => 'GAUGE',
    'p' => 'GAUGE',
    'rec_used_per' => 'GAUGE',
    'freq_used_per' => 'GAUGE',
    'arc_hits' => 'DERIVE',
    'arc_misses' => 'DERIVE',
    'demand_data_hits' => 'DERIVE',
    'demand_data_misses' => 'DERIVE',
    'demand_meta_hits' => 'DERIVE',
    'demand_meta_misses' => 'DERIVE',
    'mfu_ghost_hits' => 'DERIVE',
    'mfu_hits' => 'DERIVE',
    'mru_ghost_hits' => 'DERIVE',
    'mru_hits' => 'DERIVE',
    'pre_data_hits' => 'DERIVE',
    'pre_data_misses' => 'DERIVE',
    'pre_meta_hits' => 'DERIVE',
    'pre_meta_misses' => 'DERIVE',
    'anon_hits' => 'DERIVE',
    'arc_accesses_total' => 'DERIVE',
    'demand_data_total' => 'DERIVE',
    'pre_data_total' => 'DERIVE',
    'real_hits' => 'DERIVE',
    'cache_hits_per' => 'GAUGE',
    'cache_miss_per' => 'GAUGE',
    'actual_hit_per' => 'GAUGE',
    'data_demand_per' => 'GAUGE',
    'data_pre_per' => 'GAUGE',
    'anon_hits_per' => 'GAUGE',
    'mru_per' => 'GAUGE',
    'mfu_per' => 'GAUGE',
    'mru_ghost_per' => 'GAUGE',
    'mfu_ghost_per' => 'GAUGE',
    'demand_hits_per' => 'GAUGE',
    'pre_hits_per' => 'GAUGE',
    'meta_hits_per' => 'GAUGE',
    'pre_meta_hits_per' => 'GAUGE',
    'demand_misses_per' => 'GAUGE',
    'pre_misses_per' => 'GAUGE',
    'meta_misses_per' => 'GAUGE',
    'pre_meta_misses_per' => 'GAUGE',
];

foreach($data_sets as $ds => $type) {
    $rrd_def->addDataset($ds, $type, 0);
    $fields[$ds] = $zfs[$ds] ?? null;
}

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

// name choosen based on this is the second group of variables
$rrd_name = ['app', $name, $app->app_id, '_____group2'];
$rrd_def = RrdDefinition::make();
$fields = [];
$data_sets = [
    'l2_abort_lowmem' => 'DERIVE',
    'l2_access_total' => 'DERIVE',
    'l2_asize' => 'GAUGE',
    'l2_bufc_d_asize' => 'GAUGE',
    'l2_bufc_m_asize' => 'GAUGE',
    'l2_cksum_bad' => 'DERIVE',
    'l2_d_to_m_ratio' => 'GAUGE',
    'l2_errors' => 'DERIVE',
    'l2_evict_l1cached' => 'DERIVE',
    'l2_evict_l_retry' => 'DERIVE',
    'l2_evict_reading' => 'DERIVE',
    'l2_feeds' => 'DERIVE',
    'l2_free_on_write' => 'DERIVE',
    'l2_hdr_size' => 'GAUGE',
    'l2_hits' => 'DERIVE',
    'l2_io_error' => 'DERIVE',
    'l2_log_blk_asize' => 'GAUGE',
    'l2_log_blk_avg_as' => 'DERIVE',
    'l2_log_blk_count' => 'DERIVE',
    'l2_log_blk_writes' => 'DERIVE',
    'l2_mfu_asize' => 'GAUGE',
    'l2_misses' => 'DERIVE',
    'l2_mru_asize' => 'GAUGE',
    'l2_prefetch_asize' => 'GAUGE',
    'l2_read_bytes' => 'DERIVE',
    'l2_rb_asize' => 'GAUGE',
    'l2_rb_bufs' => 'DERIVE',
    'l2_rb_bufs_prec' => 'DERIVE',
    'l2_rb_csum_lb_err' => 'DERIVE',
    'l2_rb_dh_err' => 'DERIVE',
    'l2_rb_io_errors' => 'DERIVE',
    'l2_rb_log_blks' => 'DERIVE',
    'l2_rb_lowmem' => 'DERIVE',
    'l2_rb_size' => 'GAUGE',
    'l2_rb_success' => 'DERIVE',
    'l2_rb_unsup' => 'DERIVE',
    'l2_rw_clash' => 'DERIVE',
    'l2_size' => 'GAUGE',
    'l2_write_bytes' => 'DERIVE',
    'l2_writes_done' => 'DERIVE',
    'l2_writes_error' => 'DERIVE',
    'l2_writes_l_retry' => 'DERIVE',
    'l2_writes_sent' => 'DERIVE',
];

foreach($data_sets as $ds => $type) {
    $rrd_def->addDataset($ds, $type, 0);
    $fields[$ds] = $zfs[$ds] ?? null;
}

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

//
// process additional info returned
//

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
    app('Datastore')->put($device, 'app', $tags, $fields);

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
            app('Datastore')->put($device, 'app', $tags, $fields);
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
            Eventlog::log('ZFS pool(s) now healthy', $device['device_id'], 'application', Severity::Ok);
        } else {
            Eventlog::log('ZFS pool(s) DEGRADED, FAULTED, UNAVAIL, REMOVED, or unknown', $device['device_id'], 'application', Severity::Error);
        }
    }
} else {
    $health = 1;
}

// gets the l2 error status
if (isset($zfs['l2_errors'])) {
    $old_l2_errors = $app->data['l2_errors'] ?? 0;
    if ($old_l2_errors != $zfs['l2_errors']) {
        Eventlog::log('ZFS L2 cache has experienced errors', $device['device_id'], 'application', Severity::Error);
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
    Eventlog::log($log_message, $device['device_id'], 'application');
}

// update the app data
$app->data = [
    'pools' => $pools,
    'health' => $health,
    'version' => $all_return['version'],
    'l2_errors' => $zfs['l2_errors'] ?? null,
    'status_info' => $zpool_status,
];

update_application($app, 'OK', $metrics);
