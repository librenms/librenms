<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'ogs';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.3.111.103.115';

echo ' ' . $name;

// get data through snmp
$ogs_data = snmp_walk($device, $oid, '-Oqv', 'NET-SNMP-EXTEND-MIB');

// let librenms know that we got good data
update_application($app, $ogs_data);

// define the rrd
$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('running_jobs', 'GAUGE', 0)
    ->addDataset('pending_jobs', 'GAUGE', 0)
    ->addDataset('suspend_jobs', 'GAUGE', 0)
    ->addDataset('zombie_jobs', 'GAUGE', 0);

// parse the data from the script
$data = explode("\n", $ogs_data);
$fields = array(
    'running_jobs' => $data[0],
    'pending_jobs' => $data[1],
    'suspend_jobs' => $data[2],
    'zombie_jobs' => $data[3],
);

// push the data in an array and into the rrd
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

// cleanup
unset($ogs_data, $rrd_name, $rrd_def, $data, $fields, $tags);
