<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'postgres';
$app_id = $app['app_id'];

if (! is_array($app_data['databases'])) {
    $app_data['databases'] = [];
}

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
$databases = [];

while (isset($db_lines[$db_lines_int])) {
    [$backends, $commits, $rollbacks, $read, $hit, $idxscan, $idxtupread, $idxtupfetch, $idxblksread,
        $idxblkshit, $seqscan, $seqtupread, $ret, $fetch, $ins, $upd, $del, $dbname] = explode(' ', $db_lines[$db_lines_int]);

    $rrd_name = ['app', $name, $app_id, $dbname];

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
    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $db_lines_int++;
}
$old_databases = $app_data['databases'];

// save thge found databases
$app_data['databases'] = $databases;

//check for added databases
$added_databases = [];
foreach ($databases as $database_check) {
    $database_found = false;
    foreach ($old_databases as $database_check2) {
        if ($database_check == $database_check2) {
            $database_found = true;
        }
    }
    if (! $database_found) {
        $added_databases[] = $database_check;
    }
}

//check for removed databases
$removed_databases = [];
foreach ($old_databases as $database_check) {
    $database_found = false;
    foreach ($databases as $database_check2) {
        if ($database_check == $database_check2) {
            $database_found = true;
        }
    }
    if (! $database_found) {
        $removed_databases[] = $database_check;
    }
}

// if we have any database changes, log it
if (sizeof($added_databases) > 0 or sizeof($removed_databases) > 0) {
    $log_message = 'Postgres Database Change:';
    if (isset($added_databases[0])) {
        $log_message = $log_message . ' Added' . json_encode($added_databases);
    }
    if (isset($removed_databases[0])) {
        $log_message = $log_message . ' Removed' . json_encode($removed_databases);
    }
    log_event($log_message, $device, 'application');
}

update_application($app, $postgres, $metrics);
