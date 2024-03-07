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
    echo PHP_EOL .
        $name .
        ':' .
        $e->getCode() .
        ':' .
        $e->getMessage() .
        PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}

// RRD definition for interface+client metrics.
$rrd_def_intfclient = RrdDefinition::make()
    ->addDataset('bytes_rcvd', 'DERIVE', 0)
    ->addDataset('bytes_sent', 'DERIVE', 0)
    ->addDataset('minutes_since_last_handshake', 'GAUGE', 0);

// RRD definition for interface metrics.
$rrd_def_intf = RrdDefinition::make()
    ->addDataset('bytes_rcvd_total_intf', 'DERIVE', 0)
    ->addDataset('bytes_sent_total_intf', 'DERIVE', 0);

// RRD definition for global wireguard metrics.
$rrd_def_total = RrdDefinition::make()
    ->addDataset('bytes_rcvd_total', 'DERIVE', 0)
    ->addDataset('bytes_sent_total', 'DERIVE', 0);

$metrics = [];
$mappings = [];

$bytes_rcvd_total = null;
$bytes_sent_total = null;

// Parse json data for interfaces and their respective clients' metrics.
// Add any relevant data to the interface and global metrics within.
foreach ($interface_client_map as $interface => $client_list) {
    $bytes_rcvd_total_intf = null;
    $bytes_sent_total_intf = null;

    $finterface = is_string($interface)
        ? filter_var($interface, FILTER_SANITIZE_STRING)
        : null;

    if (is_null($finterface)) {
        echo PHP_EOL .
            $name .
            ':' .
            ' Invalid or no interface found.' .
            PHP_EOL;

        continue;
    }

    $mappings[$finterface] = [];
    foreach ($client_list as $client => $client_data) {
        $fclient = is_string($client)
            ? filter_var($client, FILTER_SANITIZE_STRING)
            : null;

        if (is_null($fclient)) {
            echo PHP_EOL .
                $name .
                ':' .
                ' Invalid or no client found.' .
                PHP_EOL;

            continue;
        }

        array_push($mappings[$finterface], $fclient);
        $bytes_rcvd = is_int($client_data['bytes_rcvd'])
            ? $client_data['bytes_rcvd']
            : null;
        $bytes_sent = is_int($client_data['bytes_sent'])
            ? $client_data['bytes_sent']
            : null;
        $minutes_since_last_handshake = is_int(
            $client_data['minutes_since_last_handshake']
        )
            ? $client_data['minutes_since_last_handshake']
            : null;

        if (is_int($bytes_rcvd)) {
            $bytes_rcvd_total_intf += $bytes_rcvd;
            $bytes_rcvd_total += $bytes_rcvd;
        }

        if (is_int($bytes_sent)) {
            $bytes_sent_total_intf += $bytes_sent;
            $bytes_sent_total += $bytes_sent;
        }

        $fields_intfclient = [
            'bytes_rcvd' => $bytes_rcvd,
            'bytes_sent' => $bytes_sent,
            'minutes_since_last_handshake' => $minutes_since_last_handshake,
        ];

        // create flattened metrics
        $metrics['intf_' . $finterface . '_client_' . $fclient] = $fields_intfclient;
        $tags_intfclient = [
            'name' => $name,
            'app_id' => $app->app_id,
            'rrd_def' => $rrd_def_intfclient,
            'rrd_name' => [
                $polling_type,
                $name,
                $app->app_id,
                $finterface,
                $fclient,
            ],
        ];
        data_update($device, $polling_type, $tags_intfclient, $fields_intfclient);
    }

    // create interface fields
    $fields_intf = [
        'bytes_rcvd_total_intf' => $bytes_rcvd_total_intf,
        'bytes_sent_total_intf' => $bytes_sent_total_intf,
    ];

    // create interface metrics
    $metrics['intf_' . $finterface] = $fields_intf;

    $tags_intf = [
        'name' => $name,
        'app_id' => $app->app_id,
        'rrd_def' => $rrd_def_intf,
        'rrd_name' => [$polling_type, $name, $app->app_id, $finterface],
    ];
    data_update($device, $polling_type, $tags_intf, $fields_intf);
}

// create total fields
$fields_all = [
    'bytes_rcvd_total' => $bytes_rcvd_total,
    'bytes_sent_total' => $bytes_sent_total,
];

// create total metrics
$metrics['global'] = $fields_all;

$tags_all = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_def' => $rrd_def_total,
    'rrd_name' => [$polling_type, $name, $app->app_id],
];
data_update($device, $polling_type, $tags_all, $fields_all);

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
    $log_message .=
        count($added_interfaces) > 0
            ? ' Added ' . implode(',', $added_interfaces)
            : '';
    $log_message .=
        count($removed_interfaces) > 0
            ? ' Removed ' . implode(',', $removed_interfaces)
            : '';
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
        $log_message .=
            count($added_clients) > 0
                ? ' Added ' . implode(',', $added_clients)
                : '';
        $log_message .=
            count($removed_clients) > 0
                ? ' Removed ' . implode(',', $removed_clients)
                : '';
        log_event($log_message, $device, 'application');
    }
}

update_application($app, $output, $metrics);
