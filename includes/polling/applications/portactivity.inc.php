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

if (is_array($ports)) {
    $ports_keys = array_keys($ports);
    $ports_keys_int = 0;
    while (isset($ports[$ports_keys[$ports_keys_int]])) {
        if (is_array($ports[$ports_keys[$ports_keys_int]])) {
            $rrd_name = ['app', $name, $app->app_id, $ports_keys[$ports_keys_int]];
            $fields = [
                'total_conns' => $ports[$ports_keys[$ports_keys_int]]['total_conns'] ?? null,
                'total_to' => $ports[$ports_keys[$ports_keys_int]]['total_to'] ?? null,
                'total_from' => $ports[$ports_keys[$ports_keys_int]]['total_from'] ?? null,
                'totalLISTEN' => $ports[$ports_keys[$ports_keys_int]]['total']['LISTEN'] ?? null,
                'totalCLOSED' => $ports[$ports_keys[$ports_keys_int]]['total']['CLOSED'] ?? null,
                'totalSYN_SENT' => $ports[$ports_keys[$ports_keys_int]]['total']['SYN_SENT'] ?? null,
                'totalSYN_RECEIVED' => $ports[$ports_keys[$ports_keys_int]]['total']['SYN_RECEIVED'] ?? null,
                'totalESTABLISHED' => $ports[$ports_keys[$ports_keys_int]]['total']['ESTABLISHED'] ?? null,
                'totalCLOSE_WAIT' => $ports[$ports_keys[$ports_keys_int]]['total']['CLOSE_WAIT'] ?? null,
                'totalFIN_WAIT_1' => $ports[$ports_keys[$ports_keys_int]]['total']['FIN_WAIT_1'] ?? null,
                'totalCLOSING' => $ports[$ports_keys[$ports_keys_int]]['total']['CLOSING'] ?? null,
                'totalLAST_ACK' => $ports[$ports_keys[$ports_keys_int]]['total']['LAST_ACK'] ?? null,
                'totalFIN_WAIT_2' => $ports[$ports_keys[$ports_keys_int]]['total']['FIN_WAIT_2'] ?? null,
                'totalTIME_WAIT' => $ports[$ports_keys[$ports_keys_int]]['total']['TIME_WAIT'] ?? null,
                'totalUNKNOWN' => $ports[$ports_keys[$ports_keys_int]]['total']['UNKNOWN'] ?? null,
                'totalother' => $ports[$ports_keys[$ports_keys_int]]['total']['other'] ?? null,
                'toLISTEN' => $ports[$ports_keys[$ports_keys_int]]['to']['LISTEN'] ?? null,
                'toCLOSED' => $ports[$ports_keys[$ports_keys_int]]['to']['CLOSED'] ?? null,
                'toSYN_SENT' => $ports[$ports_keys[$ports_keys_int]]['to']['SYN_SENT'] ?? null,
                'toSYN_RECEIVED' => $ports[$ports_keys[$ports_keys_int]]['to']['SYN_RECEIVED'] ?? null,
                'toESTABLISHED' => $ports[$ports_keys[$ports_keys_int]]['to']['ESTABLISHED'] ?? null,
                'toCLOSE_WAIT' => $ports[$ports_keys[$ports_keys_int]]['to']['CLOSE_WAIT'] ?? null,
                'toFIN_WAIT_1' => $ports[$ports_keys[$ports_keys_int]]['to']['FIN_WAIT_1'] ?? null,
                'toCLOSING' => $ports[$ports_keys[$ports_keys_int]]['to']['CLOSING'] ?? null,
                'toLAST_ACK' => $ports[$ports_keys[$ports_keys_int]]['to']['LAST_ACK'] ?? null,
                'toFIN_WAIT_2' => $ports[$ports_keys[$ports_keys_int]]['to']['FIN_WAIT_2'] ?? null,
                'toTIME_WAIT' => $ports[$ports_keys[$ports_keys_int]]['to']['TIME_WAIT'] ?? null,
                'toUNKNOWN' => $ports[$ports_keys[$ports_keys_int]]['to']['UNKNOWN'] ?? null,
                'toother' => $ports[$ports_keys[$ports_keys_int]]['to']['other'] ?? null,
                'fromLISTEN' => $ports[$ports_keys[$ports_keys_int]]['from']['LISTEN'] ?? null,
                'fromCLOSED' => $ports[$ports_keys[$ports_keys_int]]['from']['CLOSED'] ?? null,
                'fromSYN_SENT' => $ports[$ports_keys[$ports_keys_int]]['from']['SYN_SENT'] ?? null,
                'fromSYN_RECEIVED' => $ports[$ports_keys[$ports_keys_int]]['from']['SYN_RECEIVED'] ?? null,
                'fromESTABLISHED' => $ports[$ports_keys[$ports_keys_int]]['from']['ESTABLISHED'] ?? null,
                'fromCLOSE_WAIT' => $ports[$ports_keys[$ports_keys_int]]['from']['CLOSE_WAIT'] ?? null,
                'fromFIN_WAIT_1' => $ports[$ports_keys[$ports_keys_int]]['from']['FIN_WAIT_1'] ?? null,
                'fromCLOSING' => $ports[$ports_keys[$ports_keys_int]]['from']['CLOSING'] ?? null,
                'fromLAST_ACK' => $ports[$ports_keys[$ports_keys_int]]['from']['LAST_ACK'] ?? null,
                'fromFIN_WAIT_2' => $ports[$ports_keys[$ports_keys_int]]['from']['FIN_WAIT_2'] ?? null,
                'fromTIME_WAIT' => $ports[$ports_keys[$ports_keys_int]]['from']['TIME_WAIT'] ?? null,
                'fromUNKNOWN' => $ports[$ports_keys[$ports_keys_int]]['from']['UNKNOWN'] ?? null,
                'fromother' => $ports[$ports_keys[$ports_keys_int]]['from']['other'] ?? null,
            ];
            $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $ports_rrd_def, 'rrd_name' => $rrd_name];
            app('Datastore')->put($device, 'app', $tags, $fields);
        }

        $ports_keys_int++;
    }
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
    Eventlog::log($log_message, $device['device_id'], 'application');
}

update_application($app, 'OK', data_flatten($ports));
