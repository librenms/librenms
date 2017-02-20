<?php

use LibreNMS\RRD\RrdDefinition;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ntp-server"
$name = 'ntp-server';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.116.112.45.115.101.114.118.101.114';
$ntpserver_data = snmp_get($device, $oid, '-Oqv');
list ($stratum, $offset, $frequency, $jitter, $noise, $stability, $uptime, $buffer_recv, $buffer_free, $buffer_used, $packets_drop, $packets_ignore, $packets_recv, $packets_sent) = explode("\n", $ntpserver_data);

echo ' '.$name;

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('stratum', 'GAUGE', 0, 1000)
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('jitter', 'GAUGE', -1000, 1000)
    ->addDataset('noise', 'GAUGE', -1000, 1000)
    ->addDataset('stability', 'GAUGE', -1000, 1000)
    ->addDataset('uptime', 'GAUGE', 0, 125000000000)
    ->addDataset('buffer_recv', 'GAUGE', 0, 100000)
    ->addDataset('buffer_free', 'GAUGE', 0, 100000)
    ->addDataset('buffer_used', 'GAUGE', 0, 100000)
    ->addDataset('packets_drop', 'DERIVE', 0, 125000000000)
    ->addDataset('packets_ignore', 'DERIVE', 0, 125000000000)
    ->addDataset('packets_recv', 'DERIVE', 0, 125000000000)
    ->addDataset('packets_sent', 'DERIVE', 0, 125000000000);


$fields = array(
    'stratum'        => $stratum,
    'offset'         => $offset,
    'frequency'      => $frequency,
    'jitter'         => $jitter,
    'noise'          => $noise,
    'stability'      => $stability,
    'uptime'         => $uptime,
    'buffer_recv'    => $buffer_recv,
    'buffer_free'    => $buffer_free,
    'buffer_used'    => $buffer_used,
    'packets_drop'   => $packets_drop,
    'packets_ignore' => $packets_ignore,
    'packets_recv'   => $packets_recv,
    'packets_sent'   => $packets_sent,
);


$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
