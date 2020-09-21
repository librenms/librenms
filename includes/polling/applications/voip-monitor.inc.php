<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'voip-monitor';
$app_id = $app['app_id'];

echo "$name, app_id=$app_id ";

if (! empty($agent_data[$name])) {
    $rawdata = $agent_data[$name];
} else {
    $options = '-Oqv';
    $mib = 'NET-SNMP-EXTEND-MIB';

    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.118.111.105.112.109.111.110';
    $rawdata = snmp_get($device, $oid, $options, $mib);
}

// Format Data
$lines = explode("\n", $rawdata);

$voip = [];

foreach ($lines as $line) {
    [$var,$value] = explode('=', $line);
    $voip[$var] = $value;
}

unset($lines);

$rrd_name = ['app', $name, $app_id];

$rrd_def = RrdDefinition::make()
    ->addDataset('cpu', 'GAUGE', 0, 100)
    ->addDataset('kbyte', 'GAUGE', 0, 125000000000)
    ->addDataset('openfiles', 'GAUGE', 0, 125000000000);

$fields = [
    'cpu' => (float) $voip['CPU Load'],
    'kbyte' => (int) $voip['Used Memory'],
    'openfiles' => (int) $voip['Open files'],
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');

data_update($device, 'app', $tags, $fields);

update_application($app, $rawdata, $fields);
