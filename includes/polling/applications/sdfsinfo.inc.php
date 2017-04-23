<?php
/**
 * Created by PhpStorm.
 * User: crc
 * Date: 4/23/17
 * Time: 5:40 AM
 */

use LibreNMS\RRD\RrdDefinition;

$name = 'sdfsinfo';
$app_id = $app['app_id'];
$options      = '-O qv';
$oid          = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.115.100.102.115.105.110.102.111';

d_echo($name);

$sdfsinfo = snmp_walk($device, $oid, $options);

update_application($app, $sdfsinfo);
$rrd_name = array('app', $name, $app_id);

$rrd_def = RrdDefinition::make()
    ->addDataset('files', 'GAUGE', 0)
    ->addDataset('vol_capacity', 'GAUGE', 0)
    ->addDataset('vol_logic_size', 'GAUGE', 0)
    ->addDataset('vol_max_load', 'GAUGE', 0)
    ->addDataset('dup_data', 'GAUGE', 0)
    ->addDataset('blocks_unique', 'GAUGE', 0)
    ->addDataset('blocks_compressed', 'GAUGE', 0)
    ->addDataset('cluster_copies', 'GAUGE', 0)
    ->addDataset('dedup_rate', 'GAUGE', 0)
    ->addDataset('actual_savings', 'GAUGE', 0)
    ->addDataset('comp_rate', 'GAUGE', 0);

list($files, $vol_capacity, $vol_logic_size, $vol_max_load, $dup_data, $blocks_unique, $blocks_compressed, $cluster_copies, $dedup_rate, $actual_savings, $comp_rate)=explode("\n", $sdfsinfo);

$fields = array(
    'files' => $files,
    'vol_capacity' => $vol_capacity,
    'vol_logic_size' => $vol_logic_size,
    'vol_max_load' => $vol_max_load,
    'dup_data' => $dup_data,
    'blocks_unique' => $blocks_unique,
    'blocks_compressed' => $blocks_compressed,
    'cluster_copies' => $cluster_copies,
    'dedup_rate' => $dedup_rate,
    'actual_savings' => $actual_savings,
    'comp_rate' => $comp_rate,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
unset($sdfsinfo, $rrd_name, $rrd_def, $data, $fields, $tags);