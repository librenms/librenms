<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'nfsstat';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.4';

echo ' ' . $name;

$nfsstats = snmp_walk($device, $oid, '-Oqv', 'NET-SNMP-EXTEND-MIB');

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('total', 'GAUGE', 0)
    ->addDataset('null', 'GAUGE', 0)
    ->addDataset('getattr', 'GAUGE', 0)
    ->addDataset('setattr', 'GAUGE', 0)
    ->addDataset('lookup', 'GAUGE', 0)
    ->addDataset('access', 'GAUGE', 0)
    ->addDataset('read', 'GAUGE', 0)
    ->addDataset('write', 'GAUGE', 0)
    ->addDataset('create', 'GAUGE', 0)
    ->addDataset('mkdir', 'GAUGE', 0)
    ->addDataset('remove', 'GAUGE', 0)
    ->addDataset('rmdir', 'GAUGE', 0)
    ->addDataset('rename', 'GAUGE', 0)
    ->addDataset('readdirplus', 'GAUGE', 0)
    ->addDataset('fsstat', 'GAUGE', 0);

$data = explode("\n", $nfsstats);
$fields = [
    'total' => $data[0],
    'null' => $data[1],
    'getattr' => $data[2],
    'setattr' => $data[3],
    'lookup' => $data[4],
    'access' => $data[5],
    'read' => $data[6],
    'write' => $data[7],
    'create' => $data[8],
    'mkdir' => $data[9],
    'remove' => $data[10],
    'rmdir' => $data[11],
    'rename' => $data[12],
    'readdirplus' => $data[13],
    'fsstat' => $data[14],
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $nfsstats, $fields);

unset($nfsstats, $rrd_name, $rrd_def, $data, $fields, $tags);
