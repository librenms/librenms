<?php

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

$ports = $returned['data'];

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

//
// update the RRD files for each port
//

$ports_keys = array_keys($ports);
$ports_keys_int = 0;
while (isset($ports[$ports_keys[$ports_keys_int]])) {
    $rrd_name = ['app', $name, $app->app_id, $ports_keys[$ports_keys_int]];
    $fields = [
        'total_conns' => $ports[$ports_keys[$ports_keys_int]]['total_conns'],
        'total_to' => $ports[$ports_keys[$ports_keys_int]]['total_to'],
        'total_from' => $ports[$ports_keys[$ports_keys_int]]['total_from'],
        'totalLISTEN' => $ports[$ports_keys[$ports_keys_int]]['total']['LISTEN'],
        'totalCLOSED' => $ports[$ports_keys[$ports_keys_int]]['total']['CLOSED'],
        'totalSYN_SENT' => $ports[$ports_keys[$ports_keys_int]]['total']['SYN_SENT'],
        'totalSYN_RECEIVED' => $ports[$ports_keys[$ports_keys_int]]['total']['SYN_RECEIVED'],
        'totalESTABLISHED' => $ports[$ports_keys[$ports_keys_int]]['total']['ESTABLISHED'],
        'totalCLOSE_WAIT' => $ports[$ports_keys[$ports_keys_int]]['total']['CLOSE_WAIT'],
        'totalFIN_WAIT_1' => $ports[$ports_keys[$ports_keys_int]]['total']['FIN_WAIT_1'],
        'totalCLOSING' => $ports[$ports_keys[$ports_keys_int]]['total']['CLOSING'],
        'totalLAST_ACK' => $ports[$ports_keys[$ports_keys_int]]['total']['LAST_ACK'],
        'totalFIN_WAIT_2' => $ports[$ports_keys[$ports_keys_int]]['total']['FIN_WAIT_2'],
        'totalTIME_WAIT' => $ports[$ports_keys[$ports_keys_int]]['total']['TIME_WAIT'],
        'totalUNKNOWN' => $ports[$ports_keys[$ports_keys_int]]['total']['UNKNOWN'],
        'totalother' => $ports[$ports_keys[$ports_keys_int]]['total']['other'],
        'toLISTEN' => $ports[$ports_keys[$ports_keys_int]]['to']['LISTEN'],
        'toCLOSED' => $ports[$ports_keys[$ports_keys_int]]['to']['CLOSED'],
        'toSYN_SENT' => $ports[$ports_keys[$ports_keys_int]]['to']['SYN_SENT'],
        'toSYN_RECEIVED' => $ports[$ports_keys[$ports_keys_int]]['to']['SYN_RECEIVED'],
        'toESTABLISHED' => $ports[$ports_keys[$ports_keys_int]]['to']['ESTABLISHED'],
        'toCLOSE_WAIT' => $ports[$ports_keys[$ports_keys_int]]['to']['CLOSE_WAIT'],
        'toFIN_WAIT_1' => $ports[$ports_keys[$ports_keys_int]]['to']['FIN_WAIT_1'],
        'toCLOSING' => $ports[$ports_keys[$ports_keys_int]]['to']['CLOSING'],
        'toLAST_ACK' => $ports[$ports_keys[$ports_keys_int]]['to']['LAST_ACK'],
        'toFIN_WAIT_2' => $ports[$ports_keys[$ports_keys_int]]['to']['FIN_WAIT_2'],
        'toTIME_WAIT' => $ports[$ports_keys[$ports_keys_int]]['to']['TIME_WAIT'],
        'toUNKNOWN' => $ports[$ports_keys[$ports_keys_int]]['to']['UNKNOWN'],
        'toother' => $ports[$ports_keys[$ports_keys_int]]['to']['other'],
        'fromLISTEN' => $ports[$ports_keys[$ports_keys_int]]['from']['LISTEN'],
        'fromCLOSED' => $ports[$ports_keys[$ports_keys_int]]['from']['CLOSED'],
        'fromSYN_SENT' => $ports[$ports_keys[$ports_keys_int]]['from']['SYN_SENT'],
        'fromSYN_RECEIVED' => $ports[$ports_keys[$ports_keys_int]]['from']['SYN_RECEIVED'],
        'fromESTABLISHED' => $ports[$ports_keys[$ports_keys_int]]['from']['ESTABLISHED'],
        'fromCLOSE_WAIT' => $ports[$ports_keys[$ports_keys_int]]['from']['CLOSE_WAIT'],
        'fromFIN_WAIT_1' => $ports[$ports_keys[$ports_keys_int]]['from']['FIN_WAIT_1'],
        'fromCLOSING' => $ports[$ports_keys[$ports_keys_int]]['from']['CLOSING'],
        'fromLAST_ACK' => $ports[$ports_keys[$ports_keys_int]]['from']['LAST_ACK'],
        'fromFIN_WAIT_2' => $ports[$ports_keys[$ports_keys_int]]['from']['FIN_WAIT_2'],
        'fromTIME_WAIT' => $ports[$ports_keys[$ports_keys_int]]['from']['TIME_WAIT'],
        'fromUNKNOWN' => $ports[$ports_keys[$ports_keys_int]]['from']['UNKNOWN'],
        'fromother' => $ports[$ports_keys[$ports_keys_int]]['from']['other'],
    ];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $ports_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);

    $ports_keys_int++;
}

// check for added or removed instances
$old_ports = $app->data['ports'] ?? [];
$added_ports = array_diff($ports_keys, $old_ports);
$removed_ports = array_diff($old_ports, $ports_keys);

// if we have any source instances, save and log
if (count($added_ports) > 0 || count($removed_ports) > 0) {
    $app->data = ['ports' => $ports_keys];
    $log_message = 'Portactivity Port Change:';
    $log_message .= count($added_ports) > 0 ? ' Added ' . implode(',', $added_ports) : '';
    $log_message .= count($removed_ports) > 0 ? ' Removed ' . implode(',', $added_ports) : '';
    log_event($log_message, $device, 'application');
}

update_application($app, 'OK', data_flatten($ports));
