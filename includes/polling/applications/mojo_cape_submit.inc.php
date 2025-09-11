<?php

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'mojo_cape_submit';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . "$name:{$e->getCode()}:{$e->getMessage()}" . PHP_EOL;
    update_application($app, "{$e->getCode()}:{$e->getMessage()}");

    return;
}

$totals = $data['totals'];
$slugs = $data['slugs'];
$metricKeys = [
    'app_protos',
    'hash_changed',
    'size_max',
    'size_mean',
    'size_median',
    'size_min',
    'size_mode',
    'size_stddev',
    'size_sum',
    'sub_count',
];

$rrd_def = RrdDefinition::make()
    ->addDataset('app_protos', 'GAUGE', 0)
    ->addDataset('hash_changed', 'GAUGE', 0)
    ->addDataset('size_max', 'GAUGE', 0)
    ->addDataset('size_mean', 'GAUGE', 0)
    ->addDataset('size_median', 'GAUGE', 0)
    ->addDataset('size_min', 'GAUGE', 0)
    ->addDataset('size_mode', 'GAUGE', 0)
    ->addDataset('size_stddev', 'GAUGE', 0)
    ->addDataset('size_sum', 'GAUGE', 0)
    ->addDataset('sub_count', 'GAUGE', 0);

// update totals
$rrd_name = ['app', $name, $app->app_id];
$fields = array_replace(array_fill_keys($metricKeys, 0), $totals);
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name]; // reuse tags

app('Datastore')->put($device, 'app', $tags, $fields);

// Update slug data
$zero_fields = array_fill_keys($metricKeys, 0);
$existing_slugs = $app->data['slugs'] ?? [];
$new_slugs = [];
$seen_slugs = [];

foreach ($slugs as $slug => $slug_data) {
    $fields = array_replace($zero_fields, $slug_data);
    $tags['rrd_name'] = ['app', $name, $app->app_id, "slugs___-___$slug"];

    app('Datastore')->put($device, 'app', $tags, $fields);

    if (! isset($existing_slugs[$slug])) {
        $new_slugs[] = $slug;
    }

    $existing_slugs[$slug] = $slug_data['sub_count'];
    $seen_slugs[$slug] = true;
}

// Update slugs not seen this run with zeroed metrics
foreach (array_diff_key($existing_slugs, $seen_slugs) as $slug => $_) {
    $tags['rrd_name'] = ['app', $name, $app->app_id, "slugs___-___$slug"];
    app('Datastore')->put($device, 'app', $tags, $zero_fields);
    $existing_slugs[$slug] = 0;
}

// Log any alerts or notices
if ($totals['hash_changed'] >= 1) {
    Eventlog::log(
        'Mojo Cape Submit has received submissions with changed hashes: ' . json_encode($data['changed_hashes']),
        $device['device_id'],
        'application',
        Severity::Error
    );
}

if (! empty($new_slugs)) {
    Eventlog::log(
        'Mojo Cape Submit has seen one or more new slugs: ' . implode(',', $new_slugs),
        $device['device_id'],
        'application',
        Severity::Ok
    );
}

// Sort slugs by count descending and persist
arsort($existing_slugs);
$app->data = ['slugs' => $existing_slugs];

update_application($app, 'OK', $totals);
