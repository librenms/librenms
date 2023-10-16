<?php

require_once 'includes/systemd-shared.inc.php';

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\RRD\RrdDefinition;

$name = 'systemd';
$output = 'OK';
$polling_type = 'app';

$metrics = [];

// FIXME sigh
if (! function_exists('systemd_data_update_helper')) {
    /**
     * Performs a data update and returns the updated metrics.
     *
     * @param  string  $app_id
     * @param  class  $device
     * @param  array  $fields
     * @param  array  $metrics
     * @param  string  $name
     * @param  string  $polling_type
     * @param  class  $rrd_def
     * @param  string  $state_type
     * @return $metrics
     */
    function systemd_data_update_helper($app_id, $fields, $metrics, $name, $polling_type, $rrd_def, $state_type)
    {
        global $device;

        $rrd_name = [$polling_type, $name, $app_id, $state_type];
        $metrics[$state_type] = $fields;
        $tags = [
            'name' => $name,
            'app_id' => $app_id,
            'type' => $state_type,
            'rrd_def' => $rrd_def,
            'rrd_name' => $rrd_name,
        ];
        data_update($device, $polling_type, $tags, $fields);

        return $metrics;
    }
}

// Grab systemd json data.
try {
    $systemd_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $systemd_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), $metrics);

    return;
}

// Use the mapping variable in systemd-shared.inc.php to parse
// the json data received from the systemd.py script.
foreach ($systemd_mapper as $state_type => $state_statuses) {
    if (! in_array($state_type, $state_type_ternary_depth)) {
        // Process systemd states that do not have three
        // levels of depth (load and active)

        $rrd_def = RrdDefinition::make();
        $fields = [];

        // Iterate through unit state type's statuses.
        foreach ($systemd_mapper[$state_type] as $state_status) {
            $field_name = $state_status;
            $field_value = $systemd_data[$state_type][$state_status] ?? null;

            // Verify data passed by application script is valid.
            // Update fields and rrd definition.
            if (is_int($field_value) || is_null($field_value)) {
                $fields[$field_name] = $field_value;
            } else {
                $log_message = 'Systemd Polling Warning: Invalid data returned by application for systemd unit ' .
                    $state_type . ' state with' . $state_status . ' state status: ' . $field_value;
                log_event($log_message, $device, 'application');
                continue;
            }
            $rrd_def->addDataset($field_name, 'GAUGE', 0);
        }
        $metrics = systemd_data_update_helper($app->app_id, $fields, $metrics, $name, $polling_type, $rrd_def, $state_type);
    } else {
        // Process systemd states that have three
        // levels of depth (sub)

        // Iterate through unit sub state types.
        foreach ($systemd_mapper[$state_type] as $sub_state_type => $sub_state_statuses) {
            $rrd_def = RrdDefinition::make();
            $fields = [];

            // Iterate through unit sub state type's statuses.
            foreach ($sub_state_statuses as $sub_state_status) {
                $field_name = $sub_state_status;
                $field_value = $systemd_data[$state_type][$sub_state_type][$sub_state_status] ?? null;

                // Verify data passed by application script is valid.
                // Update fields and rrd definition.
                if (is_int($field_value) || is_null($field_value)) {
                    $fields[$field_name] = $field_value;
                } else {
                    $log_message = 'Systemd Polling Warning: Invalid data returned by application for systemd unit ' .
                        $state_type . ' state with ' . $sub_state_type . ' sub state type with ' . $sub_state_status .
                        ' sub state status: ' . $field_value;
                    log_event($log_message, $device, 'application');
                    continue;
                }
                $rrd_def->addDataset($field_name, 'GAUGE', 0);
            }
            $flat_type = $state_type . '_' . $sub_state_type;
            $metrics = systemd_data_update_helper($app->app_id, $fields, $metrics, $name, $polling_type, $rrd_def, $flat_type);
        }
    }
}

update_application($app, $output, $metrics);
