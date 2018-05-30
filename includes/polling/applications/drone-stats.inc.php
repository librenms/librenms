<?php

use LibreNMS\RRD\RrdDefinition;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."drone-stats"
$name = 'drone-stats';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.11.100.114.111.110.101.95.115.116.97.116.115';
$options = '-O qv';
$mib = 'NET-SNMP-EXTEND-MIB';
$stats = snmp_get($device, $oid, $options, $mib);

echo ' '.$name;

list ($worker, $pending, $running) = explode("|", $stats);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('worker', 'GAUGE', 0)
    ->addDataset('pending', 'GAUGE', 0)
    ->addDataset('running', 'GAUGE', 0);

$fields = array(
    'worker' => intval(trim($worker, '"')),
    'pending' => intval(trim($pending, '"')),
    'running' => intval(trim($running, '"')),
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $stats, $fields);
