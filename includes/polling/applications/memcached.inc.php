<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'memcached';
$app_id = $app['app_id'];
if (! empty($agent_data['app']['memcached'])) {
    $data = $agent_data['app']['memcached'][$app['app_instance']];
} else {
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.9.109.101.109.99.97.99.104.101.100';
    $result = snmp_get($device, $oid, '-Oqv');
    $data = trim($result, '"');
    $data = unserialize(stripslashes(str_replace("<<<app-memcached>>>\n", '', $data)));
    $data = reset($data);
}

echo ' memcached(' . $app['app_instance'] . ')';

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('uptime', 'GAUGE', 0, 125000000000)
    ->addDataset('threads', 'GAUGE', 0, 125000000000)
    ->addDataset('rusage_user_ms', 'DERIVE', 0, 125000000000)
    ->addDataset('rusage_system_ms', 'DERIVE', 0, 125000000000)
    ->addDataset('curr_items', 'GAUGE', 0, 125000000000)
    ->addDataset('total_items', 'DERIVE', 0, 125000000000)
    ->addDataset('limit_maxbytes', 'GAUGE', 0, 125000000000)
    ->addDataset('curr_connections', 'GAUGE', 0, 125000000000)
    ->addDataset('total_connections', 'DERIVE', 0, 125000000000)
    ->addDataset('conn_structures', 'GAUGE', 0, 125000000000)
    ->addDataset('bytes', 'GAUGE', 0, 125000000000)
    ->addDataset('cmd_get', 'DERIVE', 0, 125000000000)
    ->addDataset('cmd_set', 'DERIVE', 0, 125000000000)
    ->addDataset('get_hits', 'DERIVE', 0, 125000000000)
    ->addDataset('get_misses', 'DERIVE', 0, 125000000000)
    ->addDataset('evictions', 'DERIVE', 0, 125000000000)
    ->addDataset('bytes_read', 'DERIVE', 0, 125000000000)
    ->addDataset('bytes_written', 'DERIVE', 0, 125000000000);

$fields = [
    'uptime'            => $data['uptime'],
    'threads'           => $data['threads'],
    'rusage_user_ms'    => $data['rusage_user_microseconds'],
    'rusage_system_ms'  => $data['rusage_system_microseconds'],
    'curr_items'        => $data['curr_items'],
    'total_items'       => $data['total_items'],
    'limit_maxbytes'    => $data['limit_maxbytes'],
    'curr_connections'  => $data['curr_connections'],
    'total_connections' => $data['total_connections'],
    'conn_structures'   => $data['connection_structures'],
    'bytes'             => $data['bytes'],
    'cmd_get'           => $data['cmd_get'],
    'cmd_set'           => $data['cmd_set'],
    'get_hits'          => $data['get_hits'],
    'get_misses'        => $data['get_misses'],
    'evictions'         => $data['evictions'],
    'bytes_read'        => $data['bytes_read'],
    'bytes_written'     => $data['bytes_written'],
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $result, $fields);
