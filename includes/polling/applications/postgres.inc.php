<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'postgres';

$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.112.111.115.116.103.114.101.115';
$postgres = snmp_walk($device, $oid, $options);

[$backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
    $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del] = explode("\n", $postgres);

$rrd_name = ['app', $name, $app->app_id];
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

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//process each database
$db_lines = explode("\n", $postgres);
$db_lines_int = 17;
$databases = [];

while (isset($db_lines[$db_lines_int])) {
    [$backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
        $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del, $dbname] = explode(' ', $db_lines[$db_lines_int]);

    $rrd_name = ['app', $name, $app->app_id, $dbname];

    $databases[] = $dbname;

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
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $db_lines_int++;
}

// check for added or removed databases
$old_databases = $app->data['databases'] ?? [];
$added_databases = array_diff($databases, $old_databases);
$removed_databases = array_diff($old_databases, $databases);

// if we have any database changes, save and log
if (count($added_databases) > 0 || count($removed_databases) > 0) {
    $app->data = ['databases' => $databases];
    $log_message = 'Postgres Database Change:';
    if (count($added_databases)) {
        $log_message .= ' Added ' . implode(',', $added_databases);
    }
    if (count($removed_databases)) {
        $log_message .= ' Removed ' . implode(',', $removed_databases);
    }
    log_event($log_message, $device, 'application');
}

update_application($app, $postgres, $metrics);
