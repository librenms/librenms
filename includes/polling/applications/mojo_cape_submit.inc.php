<?php

use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'mojo_cape_submit';

try {
    $data = json_app_get($device, $name)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);
    return;
}

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

// Helper function to create fields array from data
$createFields = fn($data) => [
    'app_protos' => $data['app_protos'] ?? 0,
    'hash_changed' => $data['hash_changed'] ?? 0,
    'size_max' => $data['size_max'] ?? 0,
    'size_mean' => $data['size_mean'] ?? 0,
    'size_median' => $data['size_median'] ?? 0,
    'size_min' => $data['size_min'] ?? 0,
    'size_mode' => $data['size_mode'] ?? 0,
    'size_stddev' => $data['size_stddev'] ?? 0,
    'size_sum' => $data['size_sum'] ?? 0,
    'sub_count' => $data['sub_count'] ?? 0,
];

// Store totals
$rrd_name = ['app', $name, $app->app_id];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $createFields($data['totals']));

// Process all slugs (both active and inactive)
$slugs = $app->data['slugs'] ?? [];
$new_slugs = [];
$all_slugs = array_unique(array_merge(array_keys($data['slugs']), array_keys($slugs)));
foreach ($all_slugs as $slug) {
    $slug_data = $data['slugs'][$slug] ?? null;
    $is_active = $slug_data !== null;

    $rrd_name = ['app', $name, $app->app_id, 'slugs___-___' . $slug]; // FIXME non-standard rrdname
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'slug' => $slug, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $createFields($slug_data ?? []));

    if (!isset($slugs[$slug])) {
        $new_slugs[] = $slug;
    }

    $slugs[$slug] = $is_active ? $slug_data['sub_count'] : 0; // Update slug count
}

if ($data['totals']['hash_changed'] >= 1) {
    Eventlog::log(
        'Mojo Cape Submit has received submissions with changed hashes: ' . json_encode($data['changed_hashes']),
        $device['device_id'],
        'application',
        Severity::Error
    );
}

if (!empty($new_slugs)) {
    Eventlog::log(
        'Mojo Cape Submit has seen one or more new slugs: ' . json_encode($new_slugs),
        $device['device_id'],
        'application',
        Severity::Ok
    );
}

arsort($slugs);
$app->data = ['slugs' => $slugs];
update_application($app, 'OK', $data['totals']);
