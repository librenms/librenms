<?php

use LibreNMS\RRD\RrdDefinition;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ntp-client"
$name = 'ntp-client';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.116.112.45.99.108.105.101.110.116';
$ntpclient = snmp_get($device, $oid, '-Oqv');

echo ' '.$name;

list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('jitter', 'GAUGE', -1000, 1000)
    ->addDataset('noise', 'GAUGE', -1000, 1000)
    ->addDataset('stability', 'GAUGE', -1000, 1000);

$fields = array(
    'offset' => $offset,
    'frequency' => $frequency,
    'jitter' => $jitter,
    'noise' => $noise,
    'stability' => $stability,
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
