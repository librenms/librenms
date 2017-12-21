<?php

use LibreNMS\RRD\RrdDefinition;

echo ' zfs ';

$name = 'zfs';
$app_id = $app['app_id'];
$options = '-O qv';
$mib = 'NET-SNMP-EXTEND-MIB';
$oid = 'nsExtendOutputFull.3.122.102.115';
$zfs = snmp_get($device, $oid, $options, $mib);

$lines = explode("\n", $zfs);

list($deleted, $evict_skip, $mutex_skip, $recycle_miss)=explode(',', $lines[0]);

list($arc_size, $target_size_max, $target_size_min, $target_size, $target_size_per, $arc_size_per,
    $target_size_arat, $min_size_per)=explode(',', $lines[1]);

list($mfu_size, $p, $rec_used_per, $freq_used_per)=explode(',', $lines[2]);

list($arc_hits, $arc_misses, $demand_data_hits, $demand_data_misses, $demand_meta_hits, $demand_meta_misses,
    $mfu_ghost_hits, $mfu_hits, $mru_ghost_hits, $mru_hits, $pre_data_hits, $pre_data_misses, $pre_meta_hits,
    $pre_meta_misses, $anon_hits, $arc_accesses_total, $demand_data_total, $pre_data_total, $real_hits)=explode(',', $lines[3]);

list($cache_hits_per, $cache_miss_per, $actual_hit_per, $data_demand_per, $data_pre_per, $anon_hits_per, $mru_per, $mfu_per,
    $mru_ghost_per, $mfu_ghost_per, $demand_hits_per, $pre_hits_per, $meta_hits_per, $pre_meta_hits_per, $demand_misses_per,
    $pre_misses_per, $meta_misses_per, $pre_meta_misses_per)=explode(',', $lines[4]);

$rrd_name = array('app', $name, $app_id);
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

$fields = array(
    'deleted' => $deleted,
    'evict_skip' => $evict_skip,
    'mutex_skip' => $mutex_skip,
    'recycle_miss' => $recycle_miss,
    'arc_size' => $arc_size,
    'target_size_max' => $target_size_max,
    'target_size_min' => $target_size_min,
    'target_size' => $target_size,
    'target_size_per' => $target_size_per,
    'arc_size_per' => $arc_size_per,
    'target_size_arat' => $target_size_arat,
    'min_size_per' => $min_size_per,
    'mfu_size' => $mfu_size,
    'p' => $p,
    'rec_used_per' => $rec_used_per,
    'freq_used_per' => $freq_used_per,
    'arc_hits' => $arc_hits,
    'arc_misses' => $arc_misses,
    'demand_data_hits' => $demand_data_hits,
    'demand_data_misses' => $demand_data_misses,
    'demand_meta_hits' => $demand_meta_hits,
    'demand_meta_misses' => $demand_meta_misses,
    'mfu_ghost_hits' => $mfu_ghost_hits,
    'mfu_hits' => $mfu_hits,
    'mru_ghost_hits' => $mru_ghost_hits,
    'mru_hits' => $mru_hits,
    'pre_data_hits' => $pre_data_hits,
    'pre_data_misses' => $pre_data_misses,
    'pre_meta_hits' => $pre_meta_hits,
    'pre_meta_misses' => $pre_meta_misses,
    'anon_hits' => $anon_hits,
    'arc_accesses_total' => $arc_accesses_total,
    'demand_data_total' => $demand_data_total,
    'pre_data_total' => $pre_data_total,
    'real_hits' => $real_hits,
    'cache_hits_per' => $cache_hits_per,
    'cache_miss_per' => $cache_miss_per,
    'actual_hit_per' => $actual_hit_per,
    'data_demand_per' => $data_demand_per,
    'data_pre_per' => $data_pre_per,
    'anon_hits_per' => $anon_hits_per,
    'mru_per' => $mru_per,
    'mfu_per' => $mfu_per,
    'mru_ghost_per' => $mru_ghost_per,
    'mfu_ghost_per' => $mfu_ghost_per,
    'demand_hits_per' => $demand_hits_per,
    'pre_hits_per' => $pre_hits_per,
    'meta_hits_per' => $meta_hits_per,
    'pre_meta_hits_per' => $pre_meta_hits_per,
    'demand_misses_per' => $demand_misses_per,
    'pre_misses_per' => $pre_misses_per,
    'meta_misses_per' => $meta_misses_per,
    'pre_meta_misses_per' => $pre_meta_misses_per,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);

//
// process additional info returned
//

$pools=array();
$pool_rrd_def = RrdDefinition::make()
    ->addDataset('size', 'GAUGE', 0)
    ->addDataset('alloc', 'GAUGE', 0)
    ->addDataset('free', 'GAUGE', 0)
    ->addDataset('expandsz', 'GAUGE', 0)
    ->addDataset('frag', 'GAUGE', 0)
    ->addDataset('cap', 'GAUGE', 0)
    ->addDataset('dedup', 'GAUGE', 0);

$lines_int=5;
while (isset($lines[$lines_int])) {
    $items=explode(',', $lines[$lines_int]);

    if (strcmp($items[0],'pool')==0) {
        $pools[]=$items[1];
        $rrd_name = array('app', $name, $app_id, $items[1]);
        $fields = array(
            'size' => $items[2],
            'alloc' => $items[3],
            'free' => $items[4],
            'expandsz' => $items[5],
            'frag' => $items[6],
            'cap' => $items[7],
            'dedup' => $items[8],
        );
        $tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $pool_rrd_def, 'rrd_name' => $rrd_name);
        data_update($device, 'app', $tags, $fields);
    }

    $lines_int++;
}

//
// component processing for ZFS
//
$device_id=$device['device_id'];
$options=array(
    'filter' => array(
        'device_id' => array('=', $device_id),
        'type' => array('=', 'zfs'),
     ),
);

$component=new LibreNMS\Component();
$components=$component->getComponents($device_id, $options);

// if no jails, delete fail2ban components
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

    $id = $component->getFirstComponentID($zfsc);
    $zfsc[$id]['label'] = 'ZFS';
    $zfsc[$id]['pools'] = json_encode($pools);

    $component->setComponentPrefs($device_id, $zfsc);
}
