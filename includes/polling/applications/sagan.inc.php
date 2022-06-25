<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'sagan';
$app_id = $app['app_id'];

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
    ->addDataset('uptime', 'GAUGE', 0)
    ->addDataset('total', 'GAUGE', 0)
    ->addDataset('drop', 'GAUGE', 0)
    ->addDataset('ignore', 'GAUGE', 0)
    ->addDataset('threshold', 'GAUGE', 0)
    ->addDataset('after', 'GAUGE', 0)
    ->addDataset('match', 'GAUGE', 0)
    ->addDataset('bytes', 'GAUGE', 0)
    ->addDataset('bytes_ignored', 'GAUGE', 0)
    ->addDataset('max_bytes_log_line', 'GAUGE', 0)
    ->addDataset('eps', 'GAUGE', 0)
    ->addDataset('f_total', 'GAUGE', 0)
    ->addDataset('f_dropped', 'GAUGE', 0)
    ->addDataset('f_drop_percent', 'GAUGE', 0)
    ->addDataset('drop_percent', 'GAUGE', 0)
;

// keys to add to the RRD field
$field_keys = [
    'uptime',
    'total',
    'drop',
    'ignore',
    'threshold',
    'after',
    'match',
    'bytes',
    'bytes_ignored',
    'max_bytes_log_line',
    'eps',
    'f_total',
    'f_dropped',
    'f_dropped_percent',
    'drop_percent',
];

// process each instance
$instance_list = [];
foreach ($sagan['data'] as $instance => $stats) {
    if ($instance == '.total') {
        $rrd_name = ['app', $name, $app_id];
    } else {
        $rrd_name = ['app', $name, $app_id, $instance];
        $instance_list[] = $instance;
    }

    foreach ($instance_keys as $metric_key) {
        $metrics[$instance . '_' . $metric_key] = $stats[$metric_key];
    }

    $fields = [];
    foreach ($field_keys as $field_key) {
        $fields[$field_key] = $stats[$field_key];
    }

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}

//
// all done so update the app metrics
//
update_application($app, 'OK', $metrics);

//
// component processing
//
$device_id = $device['device_id'];
$options = [
    'filter' => [
        'device_id' => ['=', $device_id],
        'type' => ['=', 'sagan'],
    ],
];

$component = new LibreNMS\Component();
$components = $component->getComponents($device_id, $options);

// if no instances, delete the components
if (empty($instance_list)) {
    if (isset($components[$device_id])) {
        foreach ($components[$device_id] as $component_id => $_unused) {
            $component->deleteComponent($component_id);
        }
    }
} else {
    if (isset($components[$device_id])) {
        $ourc = $components[$device_id];
    } else {
        $ourc = $component->createComponent($device_id, 'sagan');
    }

    // Make sure we don't readd it, just in a different order.
    sort($instance_list);

    $id = $component->getFirstComponentID($ourc);
    $ourc[$id]['label'] = 'Sagan';
    $ourc[$id]['instances'] = json_encode($instance_list);

    $component->setComponentPrefs($device_id, $ourc);
}
