<?php

require_once 'includes/ss-shared.inc.php';

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'ss';
$output_success = 'OK';
$polling_type = 'app';

$metrics = [];

// FIXME sigh
if (! function_exists('ss_data_update_helper')) {
    /**
     * Performs a data update and returns the updated metrics.
     *
     * @param  class  $device
     * @param  string  $app_id
     * @param  array  $fields
     * @param  array  $metrics
     * @param  string  $name
     * @param  string  $polling_type
     * @param  class  $rrd_def
     * @param  string  $gen_type
     * @return $metrics
     */
    function ss_data_update_helper($device, $app_id, $fields, $metrics, $name, $polling_type, $rrd_def, $gen_type)
    {
        $rrd_name = [$polling_type, $name, $app_id, $gen_type];
        $metrics[$gen_type] = $fields;
        $tags = [
            'name' => $name,
            'app_id' => $app_id,
            'type' => $gen_type,
            'rrd_def' => $rrd_def,
            'rrd_name' => $rrd_name,
        ];
        data_update($device, $polling_type, $tags, $fields);

        return $metrics;
    }
}

try {
    $polling_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $polling_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []);

    return;
}

// List to track which sockets we should graph.  If the end-user
// filtered out the socket on the host system, it will not be
// added to the list.
$allowed_sockets = [];

// List to track which address families we should graph.  If the
// end-user filtered out the socket on the host system, it will
// not be added to the list.
$allowed_afs = [];

// Use the mapping variable in ss-shared.inc.php to parse
// the json data received from the ss.py script.
foreach ($ss_section_list as $gen_type) {
    // If a socket type does not exist in the results, then it
    // was filtered out, so skip populating RRD for this type.
    if (! array_key_exists($gen_type, $polling_data)) {
        continue;
    }

    // Add the key of the data to either the allowed sockets list
    // or the allowed address families list.
    if (in_array($gen_type, $ss_socket_list)) {
        array_push($allowed_sockets, $gen_type);
    } elseif (in_array($gen_type, $ss_af_list)) {
        array_push($allowed_afs, $gen_type);
    } else {
        $fgen_type = is_string($gen_type) ? filter_var($gen_type, FILTER_SANITIZE_STRING) : null;
        $log_message = 'Socket Statistics Invalid Socket or AF Returned by Script: ' . $fgen_type;
        log_event($log_message, $device, 'application');
        continue;
    }

    // Process sockets that do not have netids.  With the exception of "netlink",
    // these are socket types.
    if (! array_key_exists($gen_type, $ss_netid_mapper)) {
        $rrd_def = RrdDefinition::make();
        $fields = [];

        // Grab a list of socket states for the socket type we are working with.
        if (array_key_exists($gen_type, $ss_socket_states_mapper)) {
            $local_ss_socket_states_mapper = $ss_socket_states_mapper[$gen_type];
        } else {
            $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
        }

        // Iterate through socket statuses.
        foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
            $field_name = $socket_state_clean_name;
            $field_value = $polling_data[$gen_type][$socket_state] ?? 0;

            // Verify data passed by application script is valid.
            // Update fields and rrd definition.
            if (is_int($field_value)) {
                $fields[$field_name] = $field_value;
            } else {
                $log_message = 'Secure Sockets Polling Warning: Invalid data returned by ';
                $log_message .= 'application for socket ' . 'type ' . $gen_type . ' with socket ';
                $log_message .= 'state' . $socket_state . '.';
                log_event($log_message, $device, 'application');
                continue;
            }
            $rrd_def->addDataset($field_name, 'GAUGE', 0);
        }
        $metrics = ss_data_update_helper($device, $app->app_id, $fields, $metrics, $name, $polling_type, $rrd_def, $gen_type);
    } else {
        // Process sockets that have netids.  These are all address families.

        // Iterate through netids.
        foreach ($ss_netid_mapper[$gen_type] as $netid) {
            // If a socket type does not exist in the results, then it
            // was filtered out, so skip populating RRD for this type.
            if (! array_key_exists($netid, $polling_data[$gen_type])) {
                continue;
            }

            // Add the key of the data to the allowed sockets list.
            if (in_array($netid, $ss_socket_list)) {
                array_push($allowed_sockets, $netid);
            } else {
                $fgen_type = is_string($gen_type) ? filter_var($gen_type, FILTER_SANITIZE_STRING) : null;
                $log_message = 'Socket Statistics Invalid Socket Returned by Script: ' . $fgen_type;
                log_event($log_message, $device, 'application');
                continue;
            }

            $rrd_def = RrdDefinition::make();
            $fields = [];

            // Grab a list of socket states for the socket type we are working with.
            if (array_key_exists($netid, $ss_socket_states_mapper)) {
                $local_ss_socket_states_mapper = $ss_socket_states_mapper[$netid];
            } else {
                $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
            }

            // Iterate through socket statuses.
            foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
                $field_name = $socket_state_clean_name;
                $field_value = $polling_data[$gen_type][$netid][$socket_state] ?? 0;

                // Verify data passed by application script is valid.
                // Update fields and rrd definition.
                if (is_int($field_value)) {
                    $fields[$field_name] = $field_value;
                } else {
                    $log_message = 'Secure Sockets Polling Warning: Invalid data returned by ';
                    $log_message .= 'application for socket ' . 'type ' . $gen_type . ' with ';
                    $log_message .= 'netid ' . $netid . ' and socket state' . $socket_state . '.';
                    log_event($log_message, $device, 'application');
                    continue;
                }
                $rrd_def->addDataset($field_name, 'GAUGE', 0);
            }
            $flat_type = $gen_type . '_' . $netid;
            $metrics = ss_data_update_helper($device, $app->app_id, $fields, $metrics, $name, $polling_type, $rrd_def, $flat_type);
        }
    }
}

// Get old allowed sockets list.
$old_allowed_sockets = $app->data['allowed_sockets'] ?? [];

// Get old allowed address families list.
$old_allowed_afs = $app->data['allowed_afs'] ?? [];

$updated_app_data = [
    'allowed_afs' => $allowed_afs,
    'allowed_sockets' => $allowed_sockets,
];

// Check for socket type changes.
$added_sockets = array_diff($allowed_sockets, $old_allowed_sockets);
$removed_sockets = array_diff($old_allowed_sockets, $allowed_sockets);
if (count($added_sockets) > 0 || count($removed_sockets) > 0) {
    $log_message = 'Socket Statistics Allowed Sockets Change:';
    $log_message .= count($added_sockets) > 0 ? ' Added ' . implode(',', $added_sockets) : '';
    $log_message .= count($removed_sockets) > 0 ? ' Removed ' . implode(',', $removed_sockets) : '';
    log_event($log_message, $device, 'application');
}

// Check for address family changes.
$added_afs = array_diff($allowed_afs, $old_allowed_afs);
$removed_afs = array_diff($old_allowed_afs, $allowed_afs);
if (count($added_afs) > 0 || count($removed_afs) > 0) {
    $log_message = 'Socket Statistics Allowed Address Families Change:';
    $log_message .= count($added_afs) > 0 ? ' Added ' . implode(',', $added_afs) : '';
    $log_message .= count($removed_afs) > 0 ? ' Removed ' . implode(',', $removed_afs) : '';
    log_event($log_message, $device, 'application');
}

$app->data = $updated_app_data;

update_application($app, $output_success, $metrics);
