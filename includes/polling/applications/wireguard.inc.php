<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'wireguard';
$output = 'OK';
$polling_type = 'app';

try {
    $interface_client_map = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $interface_client_map = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}

$rrd_name = [$polling_type, $name, $app->app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('bytes_rcvd', 'DERIVE', 0)
    ->addDataset('bytes_sent', 'DERIVE', 0)
    ->addDataset('minutes_since_last_handshake', 'GAUGE', 0);

$metrics = [];
$mappings = [];

// Parse json data for interfaces and their respective clients' metrics.
foreach ($interface_client_map as $interface => $client_list) {
    $finterface = is_string($interface) ? filter_var($interface, FILTER_SANITIZE_STRING) : null;

    if (is_null($finterface)) {
        echo PHP_EOL . $name . ':' . ' Invalid or no interface found.' . PHP_EOL;

        continue;
    }

    $mappings[$finterface] = [];
    foreach ($client_list as $client => $client_data) {
        $fclient = is_string($client) ? filter_var($client, FILTER_SANITIZE_STRING) : null;

        if (is_null($fclient)) {
            echo PHP_EOL . $name . ':' . ' Invalid or no client found.' . PHP_EOL;

            continue;
        }

        array_push($mappings[$finterface], $fclient);
        $bytes_rcvd = is_int($client_data['bytes_rcvd'])
            ? $client_data['bytes_rcvd']
            : null;
        $bytes_sent = is_int($client_data['bytes_sent'])
            ? $client_data['bytes_sent']
            : null;
        $minutes_since_last_handshake = is_int($client_data['minutes_since_last_handshake'])
            ? $client_data['minutes_since_last_handshake']
            : null;

        $fields = [
            'bytes_rcvd' => $bytes_rcvd,
            'bytes_sent' => $bytes_sent,
            'minutes_since_last_handshake' => $minutes_since_last_handshake,
        ];

        // create flattened metrics
        $metrics[$finterface . '_' . $fclient] = $fields;
        $tags = [
            'name' => $name,
            'app_id' => $app->app_id,
            'rrd_def' => $rrd_def,
            'rrd_name' => [$polling_type, $name, $app->app_id, $finterface, $fclient],
        ];
        data_update($device, $polling_type, $tags, $fields);
    }
}

// variable tracks whether we updated mappings so it only happens once
$mappings_updated = false;

// get old mappings
$old_mappings = $app->data['mappings'] ?? [];

// check for interface changes
$added_interfaces = array_diff_key($mappings, $old_mappings);
$removed_interfaces = array_diff_key($old_mappings, $mappings);
if (count($added_interfaces) > 0 || count($removed_interfaces) > 0) {
    $app->data = ['mappings' => $mappings];
    $mappings_updated = true;
    $log_message = 'Wireguard Interfaces Change:';
    $log_message .= count($added_interfaces) > 0 ? ' Added ' . implode(',', $added_interfaces) : '';
    $log_message .= count($removed_interfaces) > 0 ? ' Removed ' . implode(',', $removed_interfaces) : '';
    log_event($log_message, $device, 'application');
}

// check for client changes
foreach ($mappings as $interface => $client_list) {
    $old_client_list = $old_mappings[$interface] ?? [];

    $added_clients = array_diff($client_list, $old_client_list);
    $removed_clients = array_diff($old_client_list, $client_list);
    if (count($added_clients) > 0 || count($removed_clients) > 0) {
        if (! $mappings_updated) {
            $app->data = ['mappings' => $mappings];
            $mappings_updated = true;
        }
        $log_message = 'Wireguard Interface ' . $interface . ' Clients Change:';
        $log_message .= count($added_clients) > 0 ? ' Added ' . implode(',', $added_clients) : '';
        $log_message .= count($removed_clients) > 0 ? ' Removed ' . implode(',', $removed_clients) : '';
        log_event($log_message, $device, 'application');
    }
}

update_application($app, $output, $metrics);
