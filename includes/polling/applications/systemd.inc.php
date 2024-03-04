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
     * @param  class  $device
     * @param  string  $app_id
     * @param  array  $fields
     * @param  array  $metrics
     * @param  string  $name
     * @param  string  $polling_type
     * @param  class  $rrd_def
     * @param  string  $state_type
     * @return $metrics
     */
    function systemd_data_update_helper(
        $device,
        $app_id,
        $fields,
        $metrics,
        $name,
        $polling_type,
        $rrd_def,
        $state_type,
        $rrd_flattened_name
    ) {
        $rrd_flattened_name = is_null($rrd_flattened_name)
            ? $state_type
            : $rrd_flattened_name;
        $rrd_name = [$polling_type, $name, $app_id, $rrd_flattened_name];

        // This if block allows metric names to be kept consistent
        // regardless of whether the metric is stored in an shared
        // or individual RRD.
        if (isset($metrics[$state_type])) {
            $metrics[$state_type] = array_merge($metrics[$state_type], $fields);
        } else {
            $metrics[$state_type] = $fields;
        }

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
    echo PHP_EOL .
        $name .
        ':' .
        $e->getCode() .
        ':' .
        $e->getMessage() .
        PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), $metrics);

    return;
}

// Use the mapping variable in systemd-shared.inc.php to parse
// the json data received from the systemd.py script.
foreach ($systemd_mapper as $state_type => $state_statuses) {
    $shared_rrd_def = RrdDefinition::make();
    $shared_fields = [];
    $sub_state_type = null;
    $flattened_type = $state_type;

    // Ternary-depth systemd type check.
    if (preg_match('/^(.+)_(.+)$/', $state_type, $regex_matches)) {
        if (! in_array($regex_matches[1], $state_type_ternary_depth)) {
            continue;
        }
        $state_type = $regex_matches[1];
        $sub_state_type = $regex_matches[2];
    }

    // Iterate through unit state type's statuses.
    foreach ($state_statuses as $state_status => $rrd_location) {
        $field_value = null;
        $field_name = $state_status;

        if (is_null($sub_state_type)) {
            $field_value = $systemd_data[$state_type][$state_status] ?? null;
        } else {
            $field_value =
                $systemd_data[$state_type][$sub_state_type][$state_status] ??
                null;
        }

        // Verify data passed by application script is valid.
        if (! is_int($field_value) && ! is_null($field_value)) {
            $log_message =
                'Systemd Polling Warning: Invalid data returned by application for systemd unit ' .
                $flattened_type .
                ' state with' .
                $state_status .
                ' state status: ' .
                $field_value;
            log_event($log_message, $device, 'application');
            continue;
        }

        // New metrics MUST use the 'individual' type because
        // it is not possible to automatically update 'shared'
        // RRDs with new metrics.
        if ($rrd_location === 'individual') {
            $individual_fields = [];
            $individual_rrd_def = RrdDefinition::make();
            $individual_fields[$field_name] = $field_value;
            $individual_rrd_def->addDataset($field_name, 'GAUGE', 0);
            $rrd_flattened_type = $flattened_type . '-' . $field_name;
            $metrics = systemd_data_update_helper(
                $device,
                $app->app_id,
                $individual_fields,
                $metrics,
                $name,
                $polling_type,
                $individual_rrd_def,
                $flattened_type,
                $rrd_flattened_type
            );
            continue;
        }

        // Update shared_fields and rrd definition.
        $shared_fields[$field_name] = $field_value;
        $shared_rrd_def->addDataset($field_name, 'GAUGE', 0);
    }

    $metrics = systemd_data_update_helper(
        $device,
        $app->app_id,
        $shared_fields,
        $metrics,
        $name,
        $polling_type,
        $shared_rrd_def,
        $flattened_type,
        null
    );
}

update_application($app, $output, $metrics);
