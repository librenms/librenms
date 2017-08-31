<?php

use LibreNMS\RRD\RrdDefinition;

global $config;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."ntp-client"
$name = 'ntp-client';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.116.112.45.99.108.105.101.110.116';
$ntpclient = snmp_get($device, $oid, '-Oqv');
$ntpclient = str_replace('"', '', $ntpclient);

echo ' '.$name;

list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);

/* 
    return Like Nagios

	0 = Ok
	1 = Warning
	2 = Critical
*/

$crit = 50; // in 50 ms
$warn = 25; // warning in +- 25ms

if (isset($config['apps'][$name]['critical']) && is_numeric($config['apps'][$name]['critical'])) {
    $crit = $config['apps'][$name]['critical'];
}

if (isset($config['apps'][$name]['warning']) && is_numeric($config['apps'][$name]['warning'])) {
    $warn = $config['apps'][$name]['warning'];
}
$status=0;
if ($offset >= $crit || $offset <= (-1 * $crit)) {
    $status=2; // critical
} elseif ($offset >= $warn || $offset <= (-1 * $warn)) {
    $status=1; // warning
}

update_application($app, $ntpclient, $status);

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
