<?php

use LibreNMS\Config;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'linux_iw';
$output = 'OK';
$polling_type = 'app';

try {
    $wireless_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $wireless_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}

if (is_int($wireless_data['linux_iw_cap_lifetime'])) {
    // Prioritize the client/ap lifetime specified on the polled host
    $linux_iw_cap_lifetime = $wireless_data['linux_iw_cap_lifetime'];
} else {
    // Otherwise use the global lifetime specified under config.php
    $linux_iw_cap_lifetime = Config::get('linux_iw_cap_lifetime') ?? null;
    if (is_null($linux_iw_cap_lifetime)) {
        // Fallback to global default of 0
        $linux_iw_cap_lifetime = 0;
    }
}

$rrd_def_interface = RrdDefinition::make()
    ->addDataset('center1', 'GAUGE', 0)
    ->addDataset('center2', 'GAUGE', 0)
    ->addDataset('channel_active_time', 'GAUGE', 0)
    ->addDataset('channel_busy_time', 'GAUGE', 0)
    ->addDataset('channel', 'GAUGE', 0)
    ->addDataset('channel_receive_time', 'GAUGE', 0)
    ->addDataset('channel_transmit_time', 'GAUGE', 0)
    ->addDataset('noise', 'GAUGE', -120, 0)
    ->addDataset('txpower', 'GAUGE', 0)
    ->addDataset('width', 'GAUGE', 0);

$rrd_def_cap = RrdDefinition::make()
    ->addDataset('beacon_interval', 'GAUGE', 0)
    ->addDataset('connected_time', 'GAUGE', 0)
    ->addDataset('dtim_interval', 'GAUGE', 0)
    ->addDataset('inactive_time', 'GAUGE', 0)
    ->addDataset('rx_bitrate', 'GAUGE', 0)
    ->addDataset('rx_bytes', 'DERIVE')
    ->addDataset('rx_drop_misc', 'DERIVE', 0)
    ->addDataset('rx_duration', 'GAUGE', 0)
    ->addDataset('rx_packets', 'DERIVE', 0)
    ->addDataset('signal', 'GAUGE', -120, 0)
    ->addDataset('snr', 'GAUGE', 0, 120)
    ->addDataset('tx_bitrate', 'GAUGE', 0)
    ->addDataset('tx_bytes', 'DERIVE', 0)
    ->addDataset('tx_failed', 'DERIVE', 0)
    ->addDataset('tx_packets', 'DERIVE', 0)
    ->addDataset('tx_retries', 'DERIVE', 0);

$metrics = [];

$wireless_mappings['intf_to_mac_mappings'] = [];
$wireless_mappings['intf_to_ssid_mappings'] = [];
$wireless_mappings['intf_to_type_mappings'] = [];
$wireless_mappings['mac_to_name_mappings'] = [];

// Sanitize and set friendly name mappings.
foreach ($wireless_data['friendly_names'] as $cap_mac => $friendly_name) {
    $f_friendly_name = is_string($friendly_name) ? filter_var($friendly_name, FILTER_SANITIZE_STRING) : '';
    $wireless_mappings['mac_to_name_mappings'][$cap_mac] = $f_friendly_name;
}

// Parse json data for interfaces and their respective clients/ap metrics.
foreach ($wireless_data['interfaces'] as $interface => $wireless_intf_data) {
    $finterface = is_string($interface) ? filter_var($interface, FILTER_SANITIZE_STRING) : null;

    if (is_null($finterface)) {
        $log_message = 'Error: Wireless AP Application Invalid Non-String Interfaces Given.';
        log_event($log_message, $device, 'application');
        continue;
    }

    $rrd_name = [$polling_type, $name, $app->app_id, $finterface];

    $fields = [
        'center1' => $wireless_intf_data['center1'] ?? null,
        'center2' => $wireless_intf_data['center2'] ?? null,
        'channel_active_time' => $wireless_intf_data['channel_active_time'] ?? null,
        'channel_busy_time' => $wireless_intf_data['channel_busy_time'] ?? null,
        'channel' => $wireless_intf_data['channel'] ?? null,
        'channel_receive_time' => $wireless_intf_data['channel_receive_time'] ?? null,
        'channel_transmit_time' => $wireless_intf_data['channel_transmit_time'] ?? null,
        'noise' => $wireless_intf_data['noise'] ?? null,
        'txpower' => $wireless_intf_data['txpower'] ?? null,
        'width' => $wireless_intf_data['width'] ?? null,
    ];

    $metrics[$finterface] = $fields;

    $tags = [
        'name' => $name,
        'app_id' => $app->app_id,
        'rrd_def' => $rrd_def_interface,
        'rrd_name' => $rrd_name,
    ];

    data_update($device, $polling_type, $tags, $fields);

    // Setup interface to SSID map.
    $ssid = isset($wireless_intf_data['ssid'])
        ? filter_var($wireless_intf_data['ssid'], FILTER_SANITIZE_STRING)
        : null;
    $wireless_mappings['intf_to_ssid_mappings'][$interface] = $ssid;

    // Setup interface to interface type ("AP", "Host", etc) map.
    $intf_type = isset($wireless_intf_data['type'])
        ? filter_var($wireless_intf_data['type'], FILTER_SANITIZE_STRING)
        : null;
    $wireless_mappings['intf_to_type_mappings'][$interface] = $intf_type;

    $wireless_mappings['intf_to_mac_mappings'][$finterface] = [];

    foreach ($wireless_intf_data['caps'] as $cap_mac => $cap_data) {
        // Setup interface to MAC client/AP mappings.
        $fcap_mac = is_string($cap_mac) ? filter_var($cap_mac, FILTER_SANITIZE_STRING) : null;
        if (is_null($fcap_mac)) {
            $log_message = 'Error: Wireless AP Application Invalid Non-String Client/AP Provided.';
            log_event($log_message, $device, 'application');
            continue;
        }
        $wireless_mappings['intf_to_mac_mappings'][$finterface][$fcap_mac] = [];

        $rrd_name = [$polling_type, $name, $app->app_id, $finterface, $fcap_mac];

        $fields = [
            'beacon_interval' => $cap_data['beacon_interval'] ?? null,
            'connected_time' => $cap_data['connected_time'] ?? null,
            'dtim_interval' => $cap_data['dtim_interval'] ?? null,
            'inactive_time' => $cap_data['inactive_time'] ?? null,
            'rx_bitrate' => $cap_data['rx_bitrate'] ?? null,
            'rx_bytes' => $cap_data['rx_bytes'] ?? null,
            'rx_drop_misc' => $cap_data['rx_drop_misc'] ?? null,
            'rx_duration' => $cap_data['rx_duration'] ?? null,
            'rx_packets' => $cap_data['rx_packets'] ?? null,
            'signal' => $cap_data['signal'] ?? null,
            'snr' => $cap_data['snr'] ?? null,
            'tx_bitrate' => $cap_data['tx_bitrate'] ?? null,
            'tx_bytes' => $cap_data['tx_bytes'] ?? null,
            'tx_failed' => $cap_data['tx_failed'] ?? null,
            'tx_packets' => $cap_data['tx_packets'] ?? null,
            'tx_retries' => $cap_data['tx_retries'] ?? null,
        ];

        // create flattened metrics
        $metrics[$finterface . '_' . $fcap_mac] = $fields;

        $tags = [
            'name' => $name,
            'app_id' => $app->app_id,
            'rrd_def' => $rrd_def_cap,
            'rrd_name' => $rrd_name,
        ];

        data_update($device, $polling_type, $tags, $fields);
    }
}

// Get older, saved interface-to-mac mappings.
$saved_intf_to_mac_mappings = $app->data['intf_to_mac_mappings'] ?? [];

$date_format_string = 'Y-m-d';

// Create today's date variables.
$today_date_string = date($date_format_string);
$today_date = date_create($today_date_string);

// Create the expiration date variables based off of today's date.
$today_expiration_date = date_create($today_date_string);
date_add($today_expiration_date, date_interval_create_from_date_string($linux_iw_cap_lifetime . ' days'));
$today_expiration_date_string = date_format($today_expiration_date, $date_format_string);

// Check for state changes of any clients/AP or interfaces against the saved (old)
// interface-to-mac mappings.
foreach ($saved_intf_to_mac_mappings as $interface => $cap_data) {
    // Log deleted interfaces.
    if (! isset($wireless_mappings['intf_to_mac_mappings'][$interface])) {
        $log_message = 'Wireless AP Application Interfaces Change:';
        $log_message .= 'Interface ' . $interface . ' Removed.';
        log_event($log_message, $device, 'application');

        continue;
    }

    // Operate on clients'/APs' expiration dates.
    foreach ($cap_data as $cap => $expiration_data) {
        $current_expiration_date = $expiration_data['expiration_date'];
        $current_start_date = $expiration_data['start_date'];
        $current_linux_iw_cap_lifetime = $expiration_data['linux_iw_cap_lifetime'];

        // If the client/AP lifetime is 0, then we set the dates to empty strings and continue.
        if ($linux_iw_cap_lifetime == 0) {
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['expiration_date'] = '';
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['start_date'] = '';
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['linux_iw_cap_lifetime'] = 0;
            continue;
        }

        // If we see the client/AP in the latest poll or the user-supplied client/AP lifetime has changed,
        // then we reset date information.
        if (isset($wireless_mappings['intf_to_mac_mappings'][$interface][$cap]) || $current_linux_iw_cap_lifetime != $linux_iw_cap_lifetime) {
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['expiration_date'] = $today_expiration_date_string;
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['start_date'] = $today_date_string;
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['linux_iw_cap_lifetime'] = $linux_iw_cap_lifetime;
            $current_expiration_date = $today_expiration_date_string;
        } else {
            // Otherwise, mark missing clients/APs in the wireless mappings with their existing data.
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['expiration_date'] = $current_expiration_date;
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['start_date'] = $current_start_date;
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['linux_iw_cap_lifetime'] = $current_linux_iw_cap_lifetime;
        }

        // If we have breached the expiration date, then we remove the client/AP.
        if ($today_date > date_create($current_expiration_date)) {
            $log_message = 'Wireless AP Application ' . $interface . ' Clients/APs Change:';
            $log_message .= ' Removed Expired Client/AP ' . $cap . ' After ' . $linux_iw_cap_lifetime . ' Days Of Inactivity.';
            log_event($log_message, $device, 'application');
            if (isset($wireless_mappings['intf_to_mac_mappings'][$interface][$cap])) {
                unset($wireless_mappings['intf_to_mac_mappings'][$interface][$cap]);
            }
        }
    }
}

// Check for state changes of any clients/APs or interfaces against the new
// interface-to-mac mappings.
foreach ($wireless_mappings['intf_to_mac_mappings'] as $interface => $cap_data) {
    // Add new interfaces.
    if (! isset($saved_intf_to_mac_mappings[$interface])) {
        $log_message = 'Wireless AP Application Interfaces Change:';
        $log_message .= 'Interface ' . $interface . ' Added.';
        log_event($log_message, $device, 'application');
    }

    // Operate on clients'/APs' expiration dates.
    foreach ($cap_data as $cap => $expiration_data) {
        // Ignore any client/AP that we've looked at already.
        if (isset($saved_intf_to_mac_mappings[$interface][$cap])) {
            continue;
        }

        // A negative client/AP lifetime immediately causes a client/AP to expire.  Instead of cluttering up the logs by adding
        // and then immediately removing clients/APs, we unset the client/AP and continue.
        if ($linux_iw_cap_lifetime < 0) {
            unset($wireless_mappings['intf_to_mac_mappings'][$interface][$cap]);
            continue;
        }

        // If the client/AP lifetime is 0, then we set the dates to empty strings.
        if ($linux_iw_cap_lifetime == 0) {
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['expiration_date'] = '';
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['start_date'] = '';
        } else {
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['expiration_date'] = $today_expiration_date_string;
            $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['start_date'] = $today_date_string;
        }
        $wireless_mappings['intf_to_mac_mappings'][$interface][$cap]['linux_iw_cap_lifetime'] = $linux_iw_cap_lifetime;

        $log_message = 'Wireless AP Application ' . $interface . ' Clients/APs Change:';
        $log_message .= ' Adding New Client/AP ' . $cap . '.';
        log_event($log_message, $device, 'application');
    }
}

$app->data = $wireless_mappings;

update_application($app, $output, $metrics);
