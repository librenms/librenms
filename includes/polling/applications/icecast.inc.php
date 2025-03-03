<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'icecast';

if (! empty($agent_data[$name])) {
    $rawdata = $agent_data[$name];
} else {
    $options = '-Oqv';
    $mib = 'NET-SNMP-EXTEND-MIB';

    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.105.99.101.99.97.115.116';
    $rawdata = snmp_get($device, $oid, $options, $mib);
    $rawdata = str_replace("<<<icecast>>>\n", '', $rawdata);
}

$lines = explode("\n", $rawdata);

$icecast = [];

foreach ($lines as $line) {
    [$var,$value] = explode('=', $line);
    $icecast[$var] = $value;
}

unset($lines);

$rrd_def = RrdDefinition::make()
    ->addDataset('cpu', 'GAUGE', 0, 100)
    ->addDataset('kbyte', 'GAUGE', 0, 125000000000)
    ->addDataset('openfiles', 'GAUGE', 0, 125000000000);

$fields = [
    'cpu' => (float) $icecast['CPU Load'],
    'kbyte' => (int) $icecast['Used Memory'],
    'openfiles' => (int) $icecast['Open files'],
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];

data_update($device, 'app', $tags, $fields);

update_application($app, $rawdata, $fields);
