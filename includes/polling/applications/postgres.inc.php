<?php

$name = 'postgres';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

echo 'postgres';

$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.112.111.115.116.103.114.101.115';
$postgres = snmp_walk($device, $oid, $options);

[$backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
    $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del] = explode("\n", $postgres);

$rrd_name = ['app', $name, $app_id];
$metrics = [];

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

$fields = [
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
    'del' => $del,
];
$metrics['none'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//process each database
$db_lines = explode("\n", $postgres);
$db_lines_int = 17;
$found_dbs = [];

while (isset($db_lines[$db_lines_int])) {
    [$backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
        $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del, $dbname] = explode(' ', $db_lines[$db_lines_int]);

    $rrd_name = ['app', $name, $app_id, $dbname];

    $found_dbs[] = $dbname;

    $fields = [
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
        'del' => $del,
    ];

    $metrics[$dbname] = $fields;
    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $db_lines_int++;
}
update_application($app, $postgres, $metrics);

//
// component processing for postgres
//
$device_id = $device['device_id'];

$options = [
    'filter' => [
        'device_id' => ['=', $device_id],
        'type' => ['=', 'postgres'],
    ],
];

$component = new LibreNMS\Component();
$pg_components = $component->getComponents($device_id, $options);

if (empty($found_dbs)) {
    if (isset($pg_components[$device_id])) {
        foreach ($pg_components[$device_id] as $component_id => $_unused) {
            $component->deleteComponent($component_id);
        }
    }
} else {
    if (isset($pg_components[$device_id])) {
        $pgc = $pg_components[$device_id];
    } else {
        $pgc = $component->createComponent($device_id, 'postgres');
    }

    // Make sure we don't readd it, just in a different order.
    sort($found_dbs);

    $id = $component->getFirstComponentID($pgc);
    $pgc[$id]['label'] = 'Postgres';
    $pgc[$id]['databases'] = json_encode($found_dbs);

    $component->setComponentPrefs($device_id, $pgc);
}
