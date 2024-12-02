<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'text_blob';

try {
    $returned = json_app_get($device, $name, 1);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;

    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$data = $returned['data'];

$metrics = [
    'has_warns' => 0,
];

$old_app_data = $app->data;
$app_data = [
    'blobs' => [],
    'warns' => [],
];

if (isset($data['warns']) && is_array($data['warns']) && empty($data['warns'])) {
    $app_data['warns'] = $data['warns'];
    $metrics['has_warns'] = 1;
}

$rrd_def = RrdDefinition::make()
    ->addDataset('data', 'GAUGE');

if (isset($data['blobs']) && is_array($data['blobs']) && !array_is_list($data['blobs'])) {
    foreach ($data['blobs'] as $blob_name => $blob) {
        if (is_scalar($data['blobs'][$blob_name])) {
            $app_data=$data['blobs'][$blob_name];

            // save size info
            $stat_name = 'blobs___' . $blob_name . '___size';
            $stat_value = strlen($data['blobs'][$blob_name]);
            $rrd_name = $rrd_name = ['app', $name, $app->app_id, $stat_name];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
            data_update($device, 'app', $tags, ['data' => $stat_value]);
            $metrics[$stat_name] = $stat_value;

            // if we exit signal if we have it
            if (isset($data['blob_exit_signal']) &&
                is_array($data['blob_exit_signal']) &&
                isset($data['blob_exit_signal'][$blob_name]) &&
                is_int($data['blob_exit_signal'][$blob_name])) {
                $stat_name = 'blobs___' . $blob_name . '___exit_signal';
                $stat_value = strlen($data['blobs_exit_signal'][$blob_name]);
                $rrd_name = $rrd_name = ['app', $name, $app->app_id, $stat_name];
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
                data_update($device, 'app', $tags, ['data' => $stat_value]);
                $metrics[$stat_name] = $stat_value;
            }

            // handles if we have coredump int info
            if (isset($data['blob_has_coredump']) &&
                is_array($data['blob_has_coredump']) &&
                isset($data['blob_has_coredump'][$blob_name]) &&
                is_int($data['blob_has_coredump'][$blob_name])) {
                $stat_name = 'blobs___' . $blob_name . '___has_coredump';
                $stat_value = strlen($data['blobs_exit_signal'][$blob_name]);
                $rrd_name = $rrd_name = ['app', $name, $app->app_id, $stat_name];
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
                data_update($device, 'app', $tags, ['data' => $stat_value]);
                $metrics[$stat_name] = $stat_value;
            }

            // handles if we have exit value info
            if (isset($data['blob_exit_val']) &&
                is_array($data['blob_exit_val']) &&
                isset($data['blob_exit_val'][$blob_name]) &&
                is_int($data['blob_exit_val'][$blob_name])) {
                $stat_name = 'blobs___' . $blob_name . '___exit';
                $stat_value = strlen($data['blobs_exit_signal'][$blob_name]);
                $rrd_name = $rrd_name = ['app', $name, $app->app_id, $stat_name];
                $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
                data_update($device, 'app', $tags, ['data' => $stat_value]);
                $metrics[$stat_name] = $stat_value;
            }
        }
    }
}

$app->data = $app_data;
update_application($app, 'OK', $metrics);
