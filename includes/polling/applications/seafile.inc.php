<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'seafile';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $seafile_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $seafile_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$account_data = $seafile_data['accounts'];
$client_version = $seafile_data['devices']['client_version'];
$client_platform = $seafile_data['devices']['platform'];
$group_data = $seafile_data['groups'];
$sysinfo_data = $seafile_data['sysinfo'];

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('enabled', 'GAUGE', 0)
    ->addDataset('libraries', 'GAUGE', 0)
    ->addDataset('trashed_libraries', 'GAUGE', 0)
    ->addDataset('size_consumption', 'GAUGE', 0);
$category = 'acc';

$metrics = [];
// handling accounts
foreach ($account_data as $data) {
    $owner_name = str_replace(' ', '_', $data['owner']);
    $enabled = $data['is_active'] ? 1 : 0;
    $libraries = $data['repos'];
    $trashed_libraries = $data['trash_repos'];
    $size_consumption = $data['usage'];

    $rrd_name = ['app', $name, $app_id, $category, $owner_name];

    $fields = [
        'enabled'           => $enabled,
        'libraries'         => $libraries,
        'trashed_libraries' => $trashed_libraries,
        'size_consumption'  => $size_consumption,
    ];

    $metrics[$owner_name . '_' . $category] = $fields;
    $tags = ['name' => $owner_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// handling groups
$rrd_def = RrdDefinition::make()
    ->addDataset('count', 'GAUGE', 0);
$category = 'grp';

$group_name = 'groups';
$group_count = $group_data['count'];

$rrd_name = ['app', $name, $app_id, $category, $group_name];

$fields = [
    'count' => $group_count,
];

$metrics[$group_name . '_' . $category] = $fields;
$tags = ['name' => $group_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

// handling client version
$rrd_def = RrdDefinition::make()
    ->addDataset('version', 'GAUGE', 0);
$category = 'cltver';

foreach ($client_version as $data) {
    $version_name = $data['client_version'];
    $version_count = $data['clients'];

    $rrd_name = ['app', $name, $app_id, $category, $version_name];

    $fields = [
        'version' => $version_count,
    ];

    $metrics[$version_name . '_' . $category] = $fields;
    $tags = ['name' => $version_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// handling client platform
$rrd_def = RrdDefinition::make()
    ->addDataset('platform', 'GAUGE', 0);
$category = 'cltos';

foreach ($client_platform as $data) {
    $os_name = $data['os_name'];
    $os_count = $data['clients'];

    $rrd_name = ['app', $name, $app_id, $category, $os_name];

    $fields = [
        'platform' => $os_count,
    ];

    $metrics[$os_name . '_' . $category] = $fields;
    $tags = ['name' => $os_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

// handling sysinfo
$rrd_def = RrdDefinition::make()
    ->addDataset('connected', 'GAUGE', 0);
$category = 'sysinfo';

$sysinfo_name = 'devices';
$sysinfo_connected_devices = $sysinfo_data['current_connected_devices_count'];

$rrd_name = ['app', $name, $app_id, $category, $sysinfo_name];

$fields = [
    'connected' => $sysinfo_connected_devices,
];

$metrics[$sysinfo_name . '_' . $category] = $fields;
$tags = ['name' => $sysinfo_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

update_application($app, $output, $metrics);
