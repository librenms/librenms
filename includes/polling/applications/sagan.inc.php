<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'sagan';
$app_id = $app['app_id'];

$app_data = get_app_data($app_id);

if (! is_array($app_data['instances'])) {
    $app_data['instances'] = [];
}

try {
    $sagan = json_app_get($device, 'sagan-stats');
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

// grab  the alert here as it is the global one
$metrics = ['alert'=>$sagan['alert']];

$rrd_def = RrdDefinition::make()
    ->addDataset('after', 'GAUGE', 0)
    ->addDataset('alert', 'GAUGE', 0)
    ->addDataset('bytes', 'GAUGE', 0)
    ->addDataset('bytes_ignored', 'GAUGE', 0)
    ->addDataset('drop', 'GAUGE', 0)
    ->addDataset('drop_percent', 'GAUGE', 0)
    ->addDataset('eps', 'GAUGE', 0)
    ->addDataset('f_drop_percent', 'GAUGE', 0)
    ->addDataset('f_dropped', 'GAUGE', 0)
    ->addDataset('f_total', 'GAUGE', 0)
    ->addDataset('ignore', 'GAUGE', 0)
    ->addDataset('match', 'GAUGE', 0)
    ->addDataset('max_bytes_log_line', 'GAUGE', 0)
    ->addDataset('threshold', 'GAUGE', 0)
    ->addDataset('total', 'GAUGE', 0)
    ->addDataset('uptime', 'GAUGE', 0);

// keys to add to the RRD field
$field_keys = [
    'after',
    'alert',
    'bytes',
    'bytes_ignored',
    'drop',
    'drop_percent',
    'eps',
    'f_drop_percent',
    'f_dropped',
    'f_total',
    'ignore',
    'match',
    'max_bytes_log_line',
    'threshold',
    'total',
    'uptime',
];

// process each instance
$instances = [];
foreach ($sagan['data'] as $instance => $stats) {
    if ($instance == '.total') {
        $rrd_name = ['app', $name, $app_id];
    } else {
        $rrd_name = ['app', $name, $app_id, $instance];
        $instances[] = $instance;
    }

    $fields = [];
    foreach ($field_keys as $field_key) {
        $metrics[$instance . '_' . $field_key] = $stats[$field_key];
        $fields[$field_key] = $stats[$field_key];
    }

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}
$old_instances = $app_data['instances'];

// save thge found instances
$app_data['instances'] = $instances;
save_app_data($app_id, $app_data);

//check for added instances
$added_instances = [];
foreach ($instances as $instance_check) {
    $instance_found = false;
    foreach ($old_instances as $instance_check2) {
        if ($instance_check == $instance_check2) {
            $instance_found = true;
        }
    }
    if (! $instance_found) {
        $added_instances[] = $instance_check;
    }
}

//check for removed instances
$removed_instances = [];
foreach ($old_instances as $instance_check) {
    $instance_found = false;
    foreach ($instances as $instance_check2) {
        if ($instance_check == $instance_check2) {
            $instance_found = true;
        }
    }
    if (! $instance_found) {
        $removed_instances[] = $instance_check;
    }
}

// if we have any instance changes, log it
if (isset($added_instances[0]) or isset($removed_instances[0])) {
    $log_message = 'Sagan Instance Change:';
    if (isset($added_instances[0])) {
        $log_message = $log_message . ' Added' . json_encode($added_instances);
    }
    if (isset($removed_instances[0])) {
        $log_message = $log_message . ' Removed' . json_encode($removed_instances);
    }
    log_event($log_message, $device, 'application');
}

//
// all done so update the app metrics
//
update_application($app, 'OK', $metrics);
