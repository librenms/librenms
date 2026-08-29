<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'routinator';

try {
    $routinator = json_app_get($device, $name, 1)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$metrics = [];

$global = $routinator['global'] ?? [];
$global_fields = [
    'last_update_done' => $global['last_update_done'] ?? null,
    'last_update_duration' => $global['last_update_duration'] ?? null,
    'serial' => $global['serial'] ?? null,
    'stale_objects' => $global['stale_objects'] ?? null,
    'vrps_final' => $global['vrps_final'] ?? null,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('last_update_done', 'GAUGE', 0)
    ->addDataset('last_update_duration', 'GAUGE', 0)
    ->addDataset('serial', 'GAUGE', 0)
    ->addDataset('stale_objects', 'GAUGE', 0)
    ->addDataset('vrps_final', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $global_fields);

$metrics['none'] = $global_fields;

$repos = $routinator['repos'] ?? [];
$repos_fields = [
    'rrdp_total' => $repos['rrdp_total'] ?? 0,
    'rrdp_ok' => $repos['rrdp_ok'] ?? 0,
    'rrdp_failed' => $repos['rrdp_failed'] ?? 0,
    'rrdp_unreachable' => $repos['rrdp_unreachable'] ?? 0,
    'rrdp_duration_max' => $repos['rrdp_duration_max'] ?? 0,
    'rsync_total' => $repos['rsync_total'] ?? 0,
    'rsync_failed' => $repos['rsync_failed'] ?? 0,
    'rsync_duration_max' => $repos['rsync_duration_max'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('rrdp_total', 'GAUGE', 0)
    ->addDataset('rrdp_ok', 'GAUGE', 0)
    ->addDataset('rrdp_failed', 'GAUGE', 0)
    ->addDataset('rrdp_unreachable', 'GAUGE', 0)
    ->addDataset('rrdp_duration_max', 'GAUGE', 0)
    ->addDataset('rsync_total', 'GAUGE', 0)
    ->addDataset('rsync_failed', 'GAUGE', 0)
    ->addDataset('rsync_duration_max', 'GAUGE', 0);
$rrd_name = ['app', $name, $app->app_id, 'repos'];
$tags = ['name' => 'repos', 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $repos_fields);
$metrics['repos'] = $repos_fields;

$rtr = $routinator['rtr'] ?? [];
$rtr_fields = [
    'current_connections' => $rtr['current_connections'] ?? 0,
    'bytes_written' => $rtr['bytes_written'] ?? 0,
    'bytes_read' => $rtr['bytes_read'] ?? 0,
];
$rrd_def = RrdDefinition::make()
    ->addDataset('current_connections', 'GAUGE', 0)
    ->addDataset('bytes_written', 'COUNTER', 0)
    ->addDataset('bytes_read', 'COUNTER', 0);
$rrd_name = ['app', $name, $app->app_id, 'rtr'];
$tags = ['name' => 'rtr', 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $rtr_fields);
$metrics['rtr'] = $rtr_fields;

$tal_def = RrdDefinition::make()
    ->addDataset('total_vrps', 'GAUGE', 0)
    ->addDataset('valid_roas', 'GAUGE', 0)
    ->addDataset('pub_points_valid', 'GAUGE', 0)
    ->addDataset('pub_points_rejected', 'GAUGE', 0)
    ->addDataset('objects_invalid', 'GAUGE', 0)
    ->addDataset('roa_invalid', 'GAUGE', 0)
    ->addDataset('manifests_missing', 'GAUGE', 0)
    ->addDataset('manifests_stale', 'GAUGE', 0);
$tals = [];
foreach ($routinator['tal'] ?? [] as $tal_name => $tal_data) {
    $tals[] = $tal_name;
    $fields = [
        'total_vrps' => $tal_data['total_vrps'] ?? 0,
        'valid_roas' => $tal_data['valid_roas'] ?? 0,
        'pub_points_valid' => $tal_data['pub_points_valid'] ?? 0,
        'pub_points_rejected' => $tal_data['pub_points_rejected'] ?? 0,
        'objects_invalid' => $tal_data['objects_invalid'] ?? 0,
        'roa_invalid' => $tal_data['roa_invalid'] ?? 0,
        'manifests_missing' => $tal_data['manifests_missing'] ?? 0,
        'manifests_stale' => $tal_data['manifests_stale'] ?? 0,
    ];
    $rrd_name = ['app', $name, $app->app_id, 'tal-' . $tal_name];
    $tags = ['name' => 'tal-' . $tal_name, 'app_id' => $app->app_id, 'rrd_def' => $tal_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);
    $metrics['tal_' . $tal_name] = $fields;
}

$client_def = RrdDefinition::make()
    ->addDataset('connections', 'GAUGE', 0)
    ->addDataset('serial', 'GAUGE', 0)
    ->addDataset('serial_lag', 'GAUGE', 0)
    ->addDataset('last_update_seconds', 'GAUGE', 0)
    ->addDataset('reset_queries', 'COUNTER', 0)
    ->addDataset('serial_queries', 'COUNTER', 0)
    ->addDataset('written_bytes', 'COUNTER', 0)
    ->addDataset('read_bytes', 'COUNTER', 0)
    ->addDataset('last_reset_seconds', 'GAUGE', 0);
$clients = [];
foreach ($routinator['client'] ?? [] as $addr => $client_data) {
    $clients[] = $addr;
    $fields = [
        'connections' => $client_data['connections'] ?? 0,
        'serial' => $client_data['serial'] ?? 0,
        'serial_lag' => $client_data['serial_lag'] ?? 0,
        'last_update_seconds' => $client_data['last_update_seconds'] ?? null,
        'reset_queries' => $client_data['reset_queries'] ?? 0,
        'serial_queries' => $client_data['serial_queries'] ?? 0,
        'written_bytes' => $client_data['written_bytes'] ?? 0,
        'read_bytes' => $client_data['read_bytes'] ?? 0,
        'last_reset_seconds' => $client_data['last_reset_seconds'] ?? null,
    ];
    $rrd_name = ['app', $name, $app->app_id, 'client-' . $addr];
    $tags = ['name' => 'client-' . $addr, 'app_id' => $app->app_id, 'rrd_def' => $client_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $fields);
    // Store only the alert-worthy subset as metrics to keep the table lean.
    $metrics['client_' . $addr] = [
        'connections' => $fields['connections'],
        'serial_lag' => $fields['serial_lag'],
        'last_update_seconds' => $fields['last_update_seconds'],
    ];
}

$app->data = ['tals' => $tals, 'clients' => $clients];

update_application($app, 'OK', $metrics);
