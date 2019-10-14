<?php
$name = 'detailedcpu';
$app_id = $app['app_id'];
use LibreNMS\RRD\RrdDefinition;

$options      = '-Oqv';
$OID     = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.11.100.101.116.97.105.108.101.100.99.112.117';
$detailedcpu = snmp_walk($device, $OID, $options);

list($user, $system, $idle, $iowait, $steal) = explode("\n", $detailedcpu);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('userstats', 'GAUGE', 0)
    ->addDataset('systemstats', 'GAUGE', 0)
    ->addDataset('idlestats', 'GAUGE', 0)
    ->addDataset('iowaitstats', 'GAUGE', 0)
    ->addDataset('stealstats', 'GAUGE', 0);
    
$fields = array(
    'userstats' => $user,
    'systemstats' => $system,
    'idlestats' => $idle,
    'iowaitstats' => $iowait,
    'stealstats' => $steal
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
update_application($app, $mailq, $fields);
