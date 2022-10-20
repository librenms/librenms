<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'memcached';

if (! empty($agent_data['app']['memcached'])) {
    $data = $agent_data['app']['memcached'];
} else {
    try {
        $data = json_app_get($device, $name, '1.1')['data'];
        $data = $data['data'][$app->app_instance] ?? reset($data['data']);
    } catch (JsonAppException $e) {
        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

        return;
    }
}
echo ' memcached(' . $app->app_instance . ')';
$data = $data[$app->app_instance] ?? reset($data);  // specified instance or just the first one

$rrd_name = ['app', $name, $app->app_id];
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
    'uptime'            => $data['uptime'] ?? null,
    'threads'           => $data['threads'] ?? null,
    'rusage_user_ms'    => $data['rusage_user_microseconds'] ?? null,
    'rusage_system_ms'  => $data['rusage_system_microseconds'] ?? null,
    'curr_items'        => $data['curr_items'] ?? null,
    'total_items'       => $data['total_items'] ?? null,
    'limit_maxbytes'    => $data['limit_maxbytes'] ?? null,
    'curr_connections'  => $data['curr_connections'] ?? null,
    'total_connections' => $data['total_connections'] ?? null,
    'conn_structures'   => $data['connection_structures'] ?? null,
    'bytes'             => $data['bytes'] ?? null,
    'cmd_get'           => $data['cmd_get'] ?? null,
    'cmd_set'           => $data['cmd_set'] ?? null,
    'get_hits'          => $data['get_hits'] ?? null,
    'get_misses'        => $data['get_misses'] ?? null,
    'evictions'         => $data['evictions'] ?? null,
    'bytes_read'        => $data['bytes_read'] ?? null,
    'bytes_written'     => $data['bytes_written'] ?? null,
];

$app_id = $app->app_id;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $result, $fields);
