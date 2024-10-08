<?php

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
    'anon',
    'anon_thp',
    'burst_usec',
    'copy-on-write-faults',
    'core_sched.force_idle_usec',
    'cpu-time',
    'data-size',
    'dbytes',
    'dios',
    'elapsed-times',
    'file',
    'file_dirty',
    'file_mapped',
    'file_thp',
    'file_writeback',
    'inactive_anon',
    'inactive_file',
    'involuntary-context-switches',
    'kernel',
    'kernel_stack',
    'major-faults',
    'minor-faults',
    'nr_bursts',
    'nr_periods',
    'nr_throttled',
    'pagetables',
    'percent-cpu',
    'percent-memory',
    'percpu',
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
    'pgsteal_kswapd',
    'procs',
    'rbytes',
    'read-blocks',
    'received-messages',
    'rios',
    'rss',
    'sec_pagetables',
    'sent-messages',
    'shmem',
    'shmem_thp',
    'signals-taken',
    'slab',
    'slab_reclaimable',
    'slab_unreclaimable',
    'sock',
    'stack-size',
    'swapcached',
    'swaps',
    'system-time',
    'system_usec',
    'text-size',
    'thp_collapse_alloc',
    'thp_fault_alloc',
    'thp_swpout',
    'thp_swpout_fallback',
    'throttled_usec',
    'unevictable',
    'usage_usec',
    'user-time',
    'user_usec',
    'virtual-size',
    'vmalloc',
    'voluntary-context-switches',
    'wbytes',
    'wios',
    'workingset_activate_anon',
    'workingset_activate_file',
    'workingset_nodereclaim',
    'workingset_refault_anon',
    'workingset_refault_file',
    'workingset_restore_anon',
    'workingset_restore_file',
    'written-blocks',
    'zswap',
    'zswapped',
    'zswpin',
    'zswpout',
    'zswpwb',
    'size',
];

$gauge_vars = [
    'procs' => 1,
    'copy-on-write-faults' => 1,
    'cpu-time' => 1,
    'data-size' => 1,
    'elapsed-times' => 1,
    'involuntary-context-switches' => 1,
    'major-faults' => 1,
    'minor-faults' => 1,
    'percent-cpu' => 1,
    'percent-memory' => 1,
    'procs' => 1,
    'read-blocks' => 1,
    'received-messages' => 1,
    'rss' => 1,
    'sent-messages' => 1,
    'signals-taken' => 1,
    'stack-size' => 1,
    'swaps' => 1,
    'system-time' => 1,
    'text-size' => 1,
    'user-time' => 1,
    'virtual-size' => 1,
    'voluntary-context-switches' => 1,
    'written-blocks' => 1,
    'anon' => 1,
    'file' => 1,
    'kernel' => 1,
    'kernel_stack' => 1,
    'pagetables' => 1,
    'sec_pagetables' => 1,
    'percpu' => 1,
    'sock' => 1,
    'vmalloc' => 1,
    'shmem' => 1,
    'zswap' => 1,
    'zswapped' => 1,
    'file_mapped' => 1,
    'file_dirty' => 1,
    'file_writeback' => 1,
    'swapcached' => 1,
    'anon_thp' => 1,
    'file_thp' => 1,
    'shmem_thp' => 1,
    'inactive_anon' => 1,
    'active_anon' => 1,
    'inactive_file' => 1,
    'active_file' => 1,
    'unevictable' => 1,
    'slab_reclaimable' => 1,
    'slab_unreclaimable' => 1,
    'slab' => 1,
    'size' => 1,
];

if ($data['backend'] != 'FreeBSD') {
    unset($gauge_vars['major-faults']);
    unset($gauge_vars['minor-faults']);
}

$gauge_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE', 0);

$counter_rrd_def = RrdDefinition::make()
    ->addDataset('data', 'COUNTER', 0);

$data = $returned['data'];

$metrics = [];
$old_data = $app->data;
$new_data = [
    'backend' => $data['backend'],
    'uid_mapping' => $data['uid_mapping'],
    'oslvm_data' => [],
    'inactive' => [],
];

// process total stats, .data.totals
foreach ($stat_vars as $key => $stat) {
    if (isset($data['totals'][$stat])) {
        $var_name = 'totals_' . $stat;
        $value = $data['totals'][$stat];
        $rrd_name = ['app', $name, $app->app_id, $var_name];
        $fields = ['data' => $value];
        $metrics[$var_name] = $value;
        if (isset($gauge_vars[$stat])) {
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
        } else {
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $counter_rrd_def, 'rrd_name' => $rrd_name];
        }
        data_update($device, 'app', $tags, $fields);
    }
}

// process each oslvm under .data.oslvms
$oslvms = [];
$current_time = time();
foreach ($data['oslvms'] as $oslvms_key => $oslvms_stats) {
    $new_data['oslvm_data'][$oslvms_key] = [
        'ip' => $oslvms_stats['ip'],
        'path' => $oslvms_stats['path'],
        'seen' => $current_time,
    ];

    $oslvms[] = $oslvms_key;
    foreach ($stat_vars as $key => $stat) {
        if (isset($oslvms_stats[$stat])) {
            $var_name = 'oslvm___' . $oslvms_key . '___' . $stat;
            $value = $oslvms_stats[$stat];
            $rrd_name = ['app', $name, $app->app_id, $var_name];
            $fields = ['data' => $value];
            $metrics[$var_name] = $value;
            if (isset($gauge_vars[$stat])) {
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $gauge_rrd_def, 'rrd_name' => $rrd_name];
            } else {
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $counter_rrd_def, 'rrd_name' => $rrd_name];
            }
            data_update($device, 'app', $tags, $fields);
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
$back_till = $current_time - \LibreNMS\Config::get('apps.oslv_monitor.seen_age', 604800);
foreach ($old_data['oslvm_data'] as $key => $oslvm) {
    if (!isset($new_data['oslvm_data'][$key]) && isset($old_data['oslvm_data'][$key]['seen']) &&
        $back_till <= $old_data['oslvm_data'][$key]['seen']) {
        $new_data['oslvm_data'][$key] = $old_data['oslvm_data'][$key];
        $new_data['inactive'][] = $key;
    }
}

$app->data = $new_data;

// if we have any source instances, save and log
if (count($added_oslvms) > 0 || count($removed_oslvms) > 0) {
    $log_message = 'OSLV Change:';
    $log_message .= count($added_oslvms) > 0 ? ' Added ' . implode(',', $added_oslvms) : '';
    $log_message .= count($removed_oslvms) > 0 ? ' Removed ' . implode(',', $removed_oslvms) : '';
    log_event($log_message, $device, 'application');
}

// all done so update the app metrics
update_application($app, 'OK', $metrics);
