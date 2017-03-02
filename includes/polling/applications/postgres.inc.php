<?php
$name = 'postgres';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.112.111.115.116.103.114.101.115';
$postgres = snmp_walk($device, $oid, $options, $mib);

list($backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
    $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del) = explode("\n", $postgres);

$rrd_name = array('app', $name, $app_id);

$rrd_def = RrdDefinition::make()
    ->addDataset('backends', 'GAUGE', 0)
    ->addDataset('commits', 'DERIVE', 0)
    ->addDataset('rollbacks', 'DERIVE', 0)
    ->addDataset('read', 'DERIVE', 0)
    ->addDataset('hit', 'DERIVE', 0)
    ->addDataset('idxscan', 'DERIVE', 0)
    ->addDataset('idxtupread', 'DERIVE', 0)
    ->addDataset('idxtupfetch', 'DERIVE', 0)
    ->addDataset('idxblksread', 'DERIVE', 0)
    ->addDataset('idxblkshit', 'DERIVE', 0)
    ->addDataset('seqscan', 'DERIVE', 0)
    ->addDataset('seqtupread', 'DERIVE', 0)
    ->addDataset('ret', 'DERIVE', 0)
    ->addDataset('fetch', 'DERIVE', 0)
    ->addDataset('ins', 'DERIVE', 0)
    ->addDataset('upd', 'DERIVE', 0)
    ->addDataset('del', 'DERIVE', 0);

$fields = array(
    'backends' => $backends,
    'commits' => $commits,
    'rollbacks' => $rollbacks,
    'read' => $read,
    'hit' => $hit,
    'idxscan' => $idxscan,
    'idxtupread' => $idxtupread,
    'idxtupfetch' => $idxtupfetch,
    'idxblksread' => $idxblksread,
    'idxblkshit' => $idxblkshit,
    'seqscan' => $seqscan,
    'seqtupread' => $seqtupread,
    'ret' => $ret,
    'fetch' => $fetch,
    'ins' => $ins,
    'upd' => $upd,
    'del' => $del
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
