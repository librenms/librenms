<?php

use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'oslv_monitor';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$stat_vars = [
    'active_anon',
    'active_file',
    'burst-time',
    'copy-on-write-faults',
    'core_sched.force_idle-time',
    'cpu-time',
    'data-size',
    'dbytes',
    'dios',
    'elapsed-times',
    'inactive_file',
    'involuntary-context-switches',
    'kernel_stack',
    'major-faults',
    'minor-faults',
    'nr_bursts',
    'nr_periods',
    'nr_throttled',
    'percent-cpu',
    'percent-memory',
    'procs',
    'rbytes',
    'read-blocks',
    'received-messages',
    'rios',
    'rss',
    'sent-messages',
    'signals-taken',
    'sock',
    'stack-size',
    'swapcached',
    'swaps',
    'system-time',
    'text-size',
    'throttled-time',
    'unevictable',
    'user-time',
    'virtual-size',
    'voluntary-context-switches',
    'wbytes',
    'wios',
    'written-blocks',
    'size',
];

if (LibrenmsConfig::get('apps.oslv_monitor.linux_pg_memory_stats')) {
    array_push(
        $stat_vars,
        'pgactivate',
        'pgdeactivate',
        'pglazyfree',
        'pglazyfreed',
        'pgrefill',
        'pgscan',
        'pgscan_direct',
        'pgscan_khugepaged',
        'pgscan_kswapd',
        'pgsteal',
        'pgsteal_direct',
        'pgsteal_khugepaged',
        'pgsteal_kswapd'
    );
}
if (LibrenmsConfig::get('apps.oslv_monitor.misc_linux_memory_stats')) {
    array_push(
        $stat_vars,
        'anon',
        'file',
        'kernel',
        'kernel_stack',
        'pagetables',
        'sec_pagetables',
        'percpu',
        'vmalloc',
        'shmem',
        'file_mapped',
        'file_dirty',
        'file_writeback',
        'swapcached',
        'anon_thp',
        'file_thp',
        'shmem_thp',
        'inactive_anon',
        'active_anon',
        'slab_reclaimable',
        'slab_unreclaimable',
        'slab',
    );
}
if (LibrenmsConfig::get('apps.oslv_monitor.zswap_size')) {
    array_push(
        $stat_vars,
        'zswap',
        'zswapped',
    );
}
if (LibrenmsConfig::get('apps.oslv_monitor.zswap_activity')) {
    array_push(
        $stat_vars,
        'zswpin',
        'zswpout',
        'zswpwb',
    );
}
if (LibrenmsConfig::get('apps.oslv_monitor.workingset_stats')) {
    array_push(
        $stat_vars,
        'workingset_refault_anon',
        'workingset_refault_file',
        'workingset_activate_anon',
        'workingset_activate_file',
        'workingset_restore_anon',
        'workingset_restore_file',
        'workingset_nodereclaim',
    );
}
if (LibrenmsConfig::get('apps.oslv_monitor.thp_activity')) {
    array_push(
        $stat_vars,
        'thp_fault_alloc',
        'thp_collapse_alloc',
        'thp_swpout',
        'thp_swpout_fallback',
    );
}

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

$data = $returned['data'];

$metrics = [];
$old_data = $app->data;
if (!is_array($old_data)) {
    $old_data = [];
}
if (!isset($old_data['oslvm_data']) || !is_array($old_data['oslvm_data'])) {
    $old_data['oslvm_data'] = [];
}
$new_data = [
    'backend' => $data['backend'] ?? null,
    'uid_mapping' => $data['uid_mapping'] ?? null,
    'oslvm_data' => [],
    'inactive' => [],
    'has' => [],
];

if (isset($data['has']) && is_array($data['has'])) {
    $new_data['has'] = $data['has'];
}

// process total stats, .data.totals
if (isset($data['totals']) && is_array($data['totals'])) {
    foreach ($stat_vars as $key => $stat) {
        if (isset($data['totals'][$stat])) {
            $var_name = 'totals_' . $stat;
            $value = $data['totals'][$stat];
            $rrd_name = ['app', $name, $app->app_id, $var_name];
            $fields = ['data' => $value];
            $metrics[$var_name] = $value;
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
            app('Datastore')->put($device, 'app', $tags, $fields);
        }
    }
}

// process each oslvm under .data.oslvms
$oslvms = [];
$current_time = now()->format('U');
if (isset($data['oslvms']) && is_array($data['oslvms'])) {
    foreach ($data['oslvms'] as $oslvms_key => $oslvms_stats) {
        $new_data['oslvm_data'][$oslvms_key] = [
            'ip' => $oslvms_stats['ip'],
            'path' => $oslvms_stats['path'],
            'seen' => $current_time,
        ];

        $metrics['running_' . $oslvms_key] = 1;

        $oslvms[] = $oslvms_key;
        foreach ($stat_vars as $key => $stat) {
            if (isset($oslvms_stats[$stat])) {
                $var_name = 'oslvm___' . $oslvms_key . '___' . $stat;
                $value = $oslvms_stats[$stat];
                $rrd_name = ['app', $name, $app->app_id, $var_name];
                $fields = ['data' => $value];
                $metrics[$var_name] = $value;
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
                app('Datastore')->put($device, 'app', $tags, $fields);
            }
        }
    }
}

// check for added or removed logs
sort($oslvms);
$old_oslvms = $old_data['oslvms'] ?? [];
$added_oslvms = array_diff($oslvms, $old_oslvms);
$removed_oslvms = array_diff($old_oslvms, $oslvms);
$new_data['oslvms'] = $oslvms;

// process unseen items, save info for ones that were last seen with in the specified time
// 604800 seconds = 7 days
$back_till = $current_time - LibrenmsConfig::get('apps.oslv_monitor.seen_age', 604800);
foreach ($old_data['oslvm_data'] as $key => $oslvm) {
    if (! isset($new_data['oslvm_data'][$key]) && isset($old_data['oslvm_data'][$key]['seen']) &&
        $back_till <= $old_data['oslvm_data'][$key]['seen']) {
        $new_data['oslvm_data'][$key] = $old_data['oslvm_data'][$key];
        $new_data['inactive'][] = $key;
        $metrics['running_' . $key] = 0;
    }
}

$app->data = $new_data;

// if we have any source instances, save and log
if (count($added_oslvms) > 0 || count($removed_oslvms) > 0) {
    $log_message = 'OSLV Change:';
    $log_message .= count($added_oslvms) > 0 ? ' Added ' . implode(',', $added_oslvms) : '';
    $log_message .= count($removed_oslvms) > 0 ? ' Removed ' . implode(',', $removed_oslvms) : '';
    Eventlog::log($log_message, $device['device_id'], 'application');
}

// all done so update the app metrics
update_application($app, 'OK', $metrics);
