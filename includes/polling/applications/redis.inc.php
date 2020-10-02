<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'redis';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $redis_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $redis_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$client_data = $redis_data['Clients'];
$memory_data = $redis_data['Memory'];
$stats_data = $redis_data['Stats'];

$metrics = [];

$category = 'clients';
$fields = [
    'connected' => $client_data['connected_clients'],
    'blocked'   => $client_data['blocked_clients'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('connected', 'GAUGE', 0)
    ->addDataset('blocked', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'memory';
$fields = [
    'active' => $memory_data['allocator'],
    'allocated'   => $memory_data['allocator_allocated'],
    'resident'   => $memory_data['allocator_resident'],
    'frag_bytes'   => $memory_data['allocator_frag_bytes'],
    'rss_bytes'   => $memory_data['allocator_rss_bytes'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('active', 'GAUGE', 0)
    ->addDataset('allocated', 'GAUGE', 0)
    ->addDataset('resident', 'GAUGE', 0)
    ->addDataset('frag_bytes', 'GAUGE', 0)
    ->addDataset('rss_bytes', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'objects';
$fields = [
    'pending' => $memory_data['lazyfree_pending_objects'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('pending', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'fragmentation';
$fields = [
    'bytes' => $memory_data['mem_fragmentation_bytes'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('bytes', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'usage';
$fields = [
    'allocated' => $memory_data['used_memory'],
    'dataset' => $memory_data['used_memory_dataset'],
    'lua' => $memory_data['used_memory_lua'],
    'overhead' => $memory_data['used_memory_overhead'],
    'peak' => $memory_data['used_memory_peak'],
    'rss' => $memory_data['used_memory_rss'],
    'scripts' => $memory_data['used_memory_scripts'],
    'startup' => $memory_data['used_memory_startup'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('allocated', 'COUNTER', 0)
    ->addDataset('dataset', 'GAUGE', 0)
    ->addDataset('lua', 'GAUGE', 0)
    ->addDataset('overhead', 'GAUGE', 0)
    ->addDataset('peak', 'GAUGE', 0)
    ->addDataset('rss', 'GAUGE', 0)
    ->addDataset('scripts', 'GAUGE', 0)
    ->addDataset('startup', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'defrag';
$fields = [
    'hits' => $stats_data['active_defrag_hits'],
    'misses'   => $stats_data['active_defrag_misses'],
    'key_hits'   => $stats_data['active_defrag_key_hits'],
    'key_misses'   => $stats_data['active_defrag_key_misses'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('hits', 'GAUGE', 0)
    ->addDataset('misses', 'GAUGE', 0)
    ->addDataset('key_hits', 'GAUGE', 0)
    ->addDataset('key_misses', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'keyspace';
$fields = [
    'hits' => $stats_data['keyspace_hits'],
    'misses'   => $stats_data['keyspace_misses'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('hits', 'COUNTER', 0)
    ->addDataset('misses', 'COUNTER', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'sync';
$fields = [
    'full' => $stats_data['sync_full'],
    'ok' => $stats_data['sync_partial_ok'],
    'err' => $stats_data['sync_partial_err'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('full', 'GAUGE', 0)
    ->addDataset('ok', 'GAUGE', 0)
    ->addDataset('err', 'GAUGE', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'commands';
$fields = [
    'processed' => $stats_data['total_commands_processed'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('processed', 'COUNTER', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'connections';
$fields = [
    'received' => $stats_data['total_connections_received'],
    'rejected' => $stats_data['rejected_connections'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('received', 'COUNTER', 0)
    ->addDataset('rejected', 'COUNTER', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$category = 'net';
$fields = [
    'input_bytes' => $stats_data['total_net_input_bytes'],
    'output_bytes' => $stats_data['total_net_output_bytes'],
];
$rrd_def = RrdDefinition::make()
    ->addDataset('input_bytes', 'COUNTER', 0)
    ->addDataset('output_bytes', 'COUNTER', 0);
$rrd_name = ['app', $name, $app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

update_application($app, $output, $metrics);
