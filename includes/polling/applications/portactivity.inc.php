<?php

use App\Models\Eventlog;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'portactivity';

try {
    $returned = json_app_get($device, 'portactivity', 1);
} catch (JsonAppException $e) { // Only doing the generic one as this has no non-JSON return
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$ports_rrd_def = RrdDefinition::make()
    ->addDataset('total_conns', 'GAUGE', 0)
    ->addDataset('total_to', 'GAUGE', 0)
    ->addDataset('total_from', 'GAUGE', 0)
    ->addDataset('totalLISTEN', 'GAUGE', 0)
    ->addDataset('totalCLOSED', 'GAUGE', 0)
    ->addDataset('totalSYN_SENT', 'GAUGE', 0)
    ->addDataset('totalSYN_RECEIVED', 'GAUGE', 0)
    ->addDataset('totalESTABLISHED', 'GAUGE', 0)
    ->addDataset('totalCLOSE_WAIT', 'GAUGE', 0)
    ->addDataset('totalFIN_WAIT_1', 'GAUGE', 0)
    ->addDataset('totalCLOSING', 'GAUGE', 0)
    ->addDataset('totalLAST_ACK', 'GAUGE', 0)
    ->addDataset('totalFIN_WAIT_2', 'GAUGE', 0)
    ->addDataset('totalTIME_WAIT', 'GAUGE', 0)
    ->addDataset('totalUNKNOWN', 'GAUGE', 0)
    ->addDataset('totalother', 'GAUGE', 0)
    ->addDataset('toLISTEN', 'GAUGE', 0)
    ->addDataset('toCLOSED', 'GAUGE', 0)
    ->addDataset('toSYN_SENT', 'GAUGE', 0)
    ->addDataset('toSYN_RECEIVED', 'GAUGE', 0)
    ->addDataset('toESTABLISHED', 'GAUGE', 0)
    ->addDataset('toCLOSE_WAIT', 'GAUGE', 0)
    ->addDataset('toFIN_WAIT_1', 'GAUGE', 0)
    ->addDataset('toCLOSING', 'GAUGE', 0)
    ->addDataset('toLAST_ACK', 'GAUGE', 0)
    ->addDataset('toFIN_WAIT_2', 'GAUGE', 0)
    ->addDataset('toTIME_WAIT', 'GAUGE', 0)
    ->addDataset('toUNKNOWN', 'GAUGE', 0)
    ->addDataset('toother', 'GAUGE', 0)
    ->addDataset('fromLISTEN', 'GAUGE', 0)
    ->addDataset('fromCLOSED', 'GAUGE', 0)
    ->addDataset('fromSYN_SENT', 'GAUGE', 0)
    ->addDataset('fromSYN_RECEIVED', 'GAUGE', 0)
    ->addDataset('fromESTABLISHED', 'GAUGE', 0)
    ->addDataset('fromCLOSE_WAIT', 'GAUGE', 0)
    ->addDataset('fromFIN_WAIT_1', 'GAUGE', 0)
    ->addDataset('fromCLOSING', 'GAUGE', 0)
    ->addDataset('fromLAST_ACK', 'GAUGE', 0)
    ->addDataset('fromFIN_WAIT_2', 'GAUGE', 0)
    ->addDataset('fromTIME_WAIT', 'GAUGE', 0)
    ->addDataset('fromUNKNOWN', 'GAUGE', 0)
    ->addDataset('fromother', 'GAUGE', 0);

$ports = $returned['data'];
// Update RRD files for each port
$states = ['LISTEN', 'CLOSED', 'SYN_SENT', 'SYN_RECEIVED', 'ESTABLISHED', 'CLOSE_WAIT', 'FIN_WAIT_1', 'CLOSING', 'LAST_ACK', 'FIN_WAIT_2', 'TIME_WAIT', 'UNKNOWN', 'other'];
foreach ($ports as $port_key => $port_data) {
    $fields = [
        'total_conns' => $port_data['total_conns'] ?? null,
        'total_to' => $port_data['total_to'] ?? null,
        'total_from' => $port_data['total_from'] ?? null,
    ];

    foreach ($states as $state) {
        $fields["total$state"] = $port_data['total'][$state] ?? null;
        $fields["to$state"] = $port_data['to'][$state] ?? null;
        $fields["from$state"] = $port_data['from'][$state] ?? null;
    }

    $tags = [
        'name' => $name,
        'app_id' => $app->app_id,
        'port' => $port_key,
        'rrd_def' => $ports_rrd_def,
        'rrd_name' => ['app', $name, $app->app_id, $port_key],
    ];

    app('Datastore')->put($device, 'app', $tags, $fields);
}

// Check for added or removed ports
$ports_keys = array_keys($ports);
$old_ports = $app->data['ports'] ?? [];
$app->data = ['ports' => $ports_keys];

// Log changes if any
$added_ports = array_diff($ports_keys, $old_ports);
if ($added_ports) {
    $log_message = 'Portactivity Port Change: Added ' . implode(',', $added_ports);
    Eventlog::log($log_message, $device['device_id'], 'application');
}

$removed_ports = array_diff($old_ports, $ports_keys);
if ($removed_ports) {
    $log_message = 'Portactivity Port Change: Removed ' . implode(',', $removed_ports);
    Eventlog::log($log_message, $device['device_id'], 'application');
}

update_application($app, 'OK', Arr::dot($ports));
