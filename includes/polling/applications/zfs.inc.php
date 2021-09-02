<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'zfs';
$app_id = $app['app_id'];

echo $name;

// Is set to false later if missing keys are found.
$not_legacy = 1;

try {
    $zfs = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    //old version with out the data key
    $zfs = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app_id];
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

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
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

foreach ($zfs['pools'] as $pool) {
    $pools[] = $pool['name'];
    $rrd_name = ['app', $name, $app_id, $pool['name']];
    $fields = [
        'alloc' => $pool['alloc'],
        'size' => $pool['size'],
        'free' => $pool['free'],
        'expandsz' => $pool['expandsz'],
        'frag' => set_numeric($pool['frag'], -1),
        'cap' => $pool['cap'],
        'dedup' => $pool['dedup'],
    ];

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $pool_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    // insert flattened pool metrics into the metrics array
    foreach ($fields as $field => $value) {
        $metrics['pool_' . $pool['name'] . '_' . $field] = $value;
    }
}

//
// component processing for ZFS
//
$device_id = $device['device_id'];
$options = [
    'filter' => [
        'device_id' => ['=', $device_id],
        'type' => ['=', 'zfs'],
    ],
];

$component = new LibreNMS\Component();
$components = $component->getComponents($device_id, $options);

// if no pools, delete zfs components
if (empty($pools)) {
    if (isset($components[$device_id])) {
        foreach ($components[$device_id] as $component_id => $_unused) {
            $component->deleteComponent($component_id);
        }
    }
} else {
    if (isset($components[$device_id])) {
        $zfsc = $components[$device_id];
    } else {
        $zfsc = $component->createComponent($device_id, 'zfs');
    }

    // Make sure we don't readd it, just in a different order.
    sort($pools);

    $id = $component->getFirstComponentID($zfsc);
    $zfsc[$id]['label'] = 'ZFS';
    $zfsc[$id]['pools'] = json_encode($pools);

    $component->setComponentPrefs($device_id, $zfsc);
}

update_application($app, 'OK', $metrics);
