<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'opensips';
$app_id = $app['app_id'];

echo "$name, app_id=$app_id ";

if (! empty($agent_data[$name])) {
    $rawdata = $agent_data[$name];
} else {
    $options = '-Oqv';
    $mib = 'NET-SNMP-EXTEND-MIB';

    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.111.112.101.110.115.105.112.115';
    $rawdata = snmp_get($device, $oid, $options, $mib);
}

// Format Data
$lines = explode("\n", $rawdata);

$opensips = [];

foreach ($lines as $line) {
    [$var,$value] = explode('=', $line);
    $opensips[$var] = $value;
}

unset($lines);

$rrd_name = ['app', $name, $app_id];

$rrd_def = RrdDefinition::make()
    ->addDataset('load', 'GAUGE', 0, 100)
    ->addDataset('total_memory', 'GAUGE', 0, 125000000000)
    ->addDataset('used_memory', 'GAUGE', 0, 125000000000)
    ->addDataset('free_memory', 'GAUGE', 0, 125000000000)
    ->addDataset('openfiles', 'GAUGE', 0, 125000000000);

$fields = [
    'load' => (float) $opensips['Load Average'],
    'total_memory' => (int) $opensips['Total Memory'],
    'used_memory' => (int) $opensips['Used Memory'],
    'free_memory' => (int) $opensips['Free Memory'],
    'openfiles' => (int) $opensips['Open files'],
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');

data_update($device, 'app', $tags, $fields);

update_application($app, $rawdata, $fields);
