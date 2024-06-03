<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'sagan';

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
$metrics = ['alert' => $sagan['alert']];

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
        $rrd_name = ['app', $name, $app->app_id];
    } else {
        $rrd_name = ['app', $name, $app->app_id, $instance];
        $instances[] = $instance;
    }

    $fields = [];
    foreach ($field_keys as $field_key) {
        $metrics[$instance . '_' . $field_key] = $stats[$field_key];
        $fields[$field_key] = $stats[$field_key];
    }

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}
$old_instances = $app->app['instances'];

//check for added instances
$added_instances = array_values(array_diff($instances, $old_instances));

//check for removed instances
$removed_instances = array_values(array_diff($old_instances, $instances));

// if we have any instance changes, log it
if (sizeof($added_instances) > 0 or sizeof($removed_instances) > 0) {
    $app->data = ['instances' => $instances];
    $log_message = 'Sagan Instance Change:';
    $log_message .= count($added_instances) > 0 ? ' Added ' . json_encode($added_instances) : '';
    $log_message .= count($removed_instances) > 0 ? ' Removed ' . json_encode($added_instances) : '';
    log_event($log_message, $device, 'application');
}

//
// all done so update the app metrics
//
update_application($app, 'OK', $metrics);
