<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'nextcloud';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$data = $returned['data'];

$metrics = [];

$old_app_data = $app->data;
$app_data = [
    'users' => [],
    'user_last_seen' => [],
    'multimount' => 0,
];

if (isset($data['multimount']) && ($data['multimount'] == 0 || $data['multimount'] == 1)) {
    $all_data['multimount'] = $data['multimount'];
}

$top_level_stats = [
    'calendars',
    'disabled_apps',
    'enabled_apps',
    'encryption_enabled',
    'used',
    'user_count',
];

$multimount_stats = [
    'total',
    'free',
    'quota',
];

$user_stats = [
    'calendars',
    'free',
    'last_seen',
    'quota',
    'relative',
    'total',
    'used',
];

$rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE');

foreach ($top_level_stats as $stat) {
    if (isset($data[$stat]) && is_numeric($data[$stat])) {
        $rrd_name = $rrd_name = ['app', $name, $app->app_id, $stat];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, ['data' => $data[$stat]]);
        $metrics[$stat] = $data[$stat];
    } else {
        $metrics[$stat] = null;
    }
}

foreach ($multimount_stats as $stat) {
    if (isset($data[$stat]) && is_numeric($data[$stat])) {
        $rrd_name = $rrd_name = ['app', $name, $app->app_id, $stat];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
        data_update($device, 'app', $tags, ['data' => $data[$stat]]);
        $metrics[$stat] = $data[$stat];
    } else {
        $metrics[$stat] = null;
    }
}

foreach ($data['users'] as $user => $user_hash) {
    if (is_array($user_hash)) {
        $app_data['users'][] = $user;
        foreach ($user_stats as $stat) {
            if (isset($user_hash[$stat]) && is_numeric($user_hash[$stat])) {
                $rrd_name = $rrd_name = ['app', $name, $app->app_id, 'users___' . $user . '___' . $stat];
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
                data_update($device, 'app', $tags, ['data' => $user_hash[$stat]]);
                $metrics['users___' . $user . '___' . $stat] = $user_hash[$stat];
            } else {
                $metrics['users___' . $user . '___' . $stat] = null;
            }
        }
        if (isset($user_hash['last_seen_string']) && is_string($user_hash['last_seen_string']) && strlen($user_hash['last_seen_string']) < 128) {
            $app_data['user_last_seen'][$user] = $user_hash['last_seen_string'];
            echo 'last seen ' . $user_hash['last_seen_string'] . "\n";
        }
    }
}

$app->data = $app_data;
update_application($app, 'OK', $metrics);
