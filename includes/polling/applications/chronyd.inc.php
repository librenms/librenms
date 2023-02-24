<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'chronyd';
try {
    $chronyd = json_app_get($device, $name, 1)['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('stratum', 'GAUGE', 0, 15)
    ->addDataset('reference_time', 'DCOUNTER', 0.0, 10000000000) // good until year 2286
    ->addDataset('system_time', 'GAUGE', -10000.0, 10000.0)
    ->addDataset('last_offset', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('rms_offset', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('frequency', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('residual_frequency', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('skew', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('root_delay', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('root_dispersion', 'GAUGE', -1000.0, 1000.0)
    ->addDataset('update_interval', 'GAUGE', 0, 4096); // good for >1h

$fields = [
    'stratum'               => $chronyd['tracking']['stratum'],
    'reference_time'        => $chronyd['tracking']['reference_time'],
    'system_time'           => $chronyd['tracking']['system_time'],
    'last_offset'           => $chronyd['tracking']['last_offset'],
    'rms_offset'            => $chronyd['tracking']['rms_offset'],
    'frequency'             => $chronyd['tracking']['frequency'],
    'residual_frequency'    => $chronyd['tracking']['residual_frequency'],
    'skew'                  => $chronyd['tracking']['skew'],
    'root_delay'            => $chronyd['tracking']['root_delay'],
    'root_dispersion'       => $chronyd['tracking']['root_dispersion'],
    'update_interval'       => $chronyd['tracking']['update_interval'],
];

// $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

// process sources data

$sources = [];
$source_rrd_def = RrdDefinition::make()
    ->addDataset('stratum', 'GAUGE', 0, 15)
    ->addDataset('polling_rate', 'GAUGE', 0, 4096) // good for >1h
    ->addDataset('last_rx', 'GAUGE', 0, 777)
    ->addDataset('adjusted_offset', 'GAUGE', -10000, 10000)
    ->addDataset('measured_offset', 'GAUGE', -10000, 10000)
    ->addDataset('estimated_error', 'GAUGE', -10000, 10000)
    ->addDataset('number_samplepoints', 'GAUGE', 0, 4096)
    ->addDataset('number_runs', 'GAUGE', 0, 100)
    ->addDataset('span', 'GAUGE', 0, 10000)
    ->addDataset('frequency', 'GAUGE', -1000, 1000)
    ->addDataset('frequency_skew', 'GAUGE', -1000, 1000)
    ->addDataset('offset', 'GAUGE', -1000, 1000)
    ->addDataset('stddev', 'GAUGE', -1000, 1000);

$metrics = $chronyd;
unset($metrics['sources']);

foreach ($chronyd['sources'] as $source) {
    $sources[] = $source['source_name'];
    $rrd_name = ['app', $name, $app->app_id, $source['source_name']];
    $fields = [
        'stratum'               => $source['stratum'],
        'polling_rate'          => $source['polling_rate'],
        'last_rx'               => $source['last_rx'],
        'adjusted_offset'       => $source['adjusted_offset'],
        'measured_offset'       => $source['measured_offset'],
        'estimated_error'       => $source['estimated_error'],
        'number_samplepoints'   => $source['number_samplepoints'],
        'number_runs'           => $source['number_runs'],
        'span'                  => $source['span'],
        'frequency'             => $source['frequency'],
        'frequency_skew'        => $source['frequency_skew'],
        'offset'                => $source['offset'],
        'stddev'                => $source['stddev'],
    ];

    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $source_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    // insert flattened source metrics into the metrics array
    foreach ($fields as $field => $value) {
        $metrics['source_' . $source['source_name'] . '_' . $field] = $value;
    }
}

// check for added or removed sources
$old_sources = $app->data['sources'] ?? [];
$added_sources = array_diff($sources, $old_sources);
$removed_sources = array_diff($old_sources, $sources);

// if we have any source changes, save and log
if (count($added_sources) > 0 || count($removed_sources) > 0) {
    $app->data = ['sources' => $sources]; // save sources
    $log_message = 'Chronyd Source Change:';
    $log_message .= count($added_sources) > 0 ? ' Added ' . implode(',', $added_sources) : '';
    $log_message .= count($removed_sources) > 0 ? ' Removed ' . implode(',', $added_sources) : '';
    log_event($log_message, $device, 'application');
}

update_application($app, 'OK', $metrics);
