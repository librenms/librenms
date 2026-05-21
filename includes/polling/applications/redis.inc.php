<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'redis';
$output = 'OK';
if (! empty($agent_data['app'][$name])) {
    $parsed_json = json_decode(stripslashes((string) $agent_data['app'][$name]), true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($parsed_json) || ! isset($parsed_json['error'], $parsed_json['data'], $parsed_json['errorString'], $parsed_json['version']) || $parsed_json['version'] < 1 || $parsed_json['error'] != 0) {
        update_application($app, '-10:No correct data retrieved', []);

        return;
    }
    $redis_data = $parsed_json['data'];
} else {
    try {
        $redis_data = json_app_get($device, $name, 1)['data'];
    } catch (JsonAppMissingKeysException $e) {
        $redis_data = $e->getParsedJson();
    } catch (JsonAppException $e) {
        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

        return;
    }
}

$client_data = $redis_data['Clients'] ?? [];
$memory_data = $redis_data['Memory'] ?? [];
$stats_data = $redis_data['Stats'] ?? [];

$metrics = [];

$category = 'clients';
$fields = [
    'connected' => $client_data['connected_clients'] ?? 0,
    'blocked' => $client_data['blocked_clients'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('connected', 'GAUGE', 0)
    ->addDataset('blocked', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'memory';
$fields = [
    'active' => $memory_data['allocator_active'] ?? 0,
    'allocated' => $memory_data['allocator_allocated'] ?? 0,
    'resident' => $memory_data['allocator_resident'] ?? 0,
    'frag_bytes' => $memory_data['allocator_frag_bytes'] ?? 0,
    'rss_bytes' => $memory_data['allocator_rss_bytes'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('active', 'GAUGE', 0)
    ->addDataset('allocated', 'GAUGE', 0)
    ->addDataset('resident', 'GAUGE', 0)
    ->addDataset('frag_bytes', 'GAUGE', 0)
    ->addDataset('rss_bytes', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'objects';
$fields = [
    'pending' => $memory_data['lazyfree_pending_objects'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('pending', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'fragmentation';
$fields = [
    'bytes' => $memory_data['mem_fragmentation_bytes'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('bytes', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'usage';
$fields = [
    'allocated' => $memory_data['used_memory'] ?? 0,
    'dataset' => $memory_data['used_memory_dataset'] ?? 0,
    'lua' => $memory_data['used_memory_lua'] ?? 0,
    'overhead' => $memory_data['used_memory_overhead'] ?? 0,
    'peak' => $memory_data['used_memory_peak'] ?? 0,
    'rss' => $memory_data['used_memory_rss'] ?? 0,
    'scripts' => $memory_data['used_memory_scripts'] ?? 0,
    'startup' => $memory_data['used_memory_startup'] ?? 0,
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
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'defrag';
$fields = [
    'hits' => $stats_data['active_defrag_hits'] ?? 0,
    'misses' => $stats_data['active_defrag_misses'] ?? 0,
    'key_hits' => $stats_data['active_defrag_key_hits'] ?? 0,
    'key_misses' => $stats_data['active_defrag_key_misses'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('hits', 'GAUGE', 0)
    ->addDataset('misses', 'GAUGE', 0)
    ->addDataset('key_hits', 'GAUGE', 0)
    ->addDataset('key_misses', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'keyspace';
$fields = [
    'hits' => $stats_data['keyspace_hits'] ?? 0,
    'misses' => $stats_data['keyspace_misses'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('hits', 'COUNTER', 0)
    ->addDataset('misses', 'COUNTER', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'sync';
$fields = [
    'full' => $stats_data['sync_full'] ?? 0,
    'ok' => $stats_data['sync_partial_ok'] ?? 0,
    'err' => $stats_data['sync_partial_err'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('full', 'GAUGE', 0)
    ->addDataset('ok', 'GAUGE', 0)
    ->addDataset('err', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'commands';
$fields = [
    'processed' => $stats_data['total_commands_processed'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('processed', 'COUNTER', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'connections';
$fields = [
    'received' => $stats_data['total_connections_received'] ?? 0,
    'rejected' => $stats_data['rejected_connections'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('received', 'COUNTER', 0)
    ->addDataset('rejected', 'COUNTER', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

$category = 'net';
$fields = [
    'input_bytes' => $stats_data['total_net_input_bytes'] ?? 0,
    'output_bytes' => $stats_data['total_net_output_bytes'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('input_bytes', 'COUNTER', 0)
    ->addDataset('output_bytes', 'COUNTER', 0);
$rrd_name = ['app', $name, $app->app_id, $category];

$metrics[$category] = $fields;
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);

update_application($app, $output, $metrics);
