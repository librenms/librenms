<?php

use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\JsonAppBase64DecodeException;
use LibreNMS\Exceptions\JsonAppBlankJsonException;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\Exceptions\JsonAppGzipDecodeException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\Exceptions\JsonAppPollingFailedException;
use LibreNMS\Exceptions\JsonAppWrongVersionException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Number;
use LibreNMS\Util\UserFuncHelper;

function bulk_sensor_snmpget($device, $sensors)
{
    $oid_per_pdu = get_device_oid_limit($device);
    $sensors = array_chunk($sensors, $oid_per_pdu);
    $cache = [];
    foreach ($sensors as $chunk) {
        $oids = array_map(function ($data) {
            return $data['sensor_oid'];
        }, $chunk);
        $oids = implode(' ', $oids);
        $multi_response = snmp_get_multi_oid($device, $oids, '-OUQntea');
        $cache = array_merge($cache, $multi_response);
    }

    return $cache;
}

/**
 * @param  $device
 * @param  string  $type  type/class of sensor
 * @return array
 */
function sensor_precache($device, $type)
{
    $sensor_cache = [];
    if (file_exists('includes/polling/sensors/pre-cache/' . $device['os'] . '.inc.php')) {
        include 'includes/polling/sensors/pre-cache/' . $device['os'] . '.inc.php';
    }

    return $sensor_cache;
}

function poll_sensor($device, $class)
{
    global $agent_sensors;

    $sensors = [];
    $misc_sensors = [];
    $all_sensors = [];

    foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ?', [$class, $device['device_id']]) as $sensor) {
        if ($sensor['poller_type'] == 'agent') {
            // Agent sensors are polled in the unix-agent
        } elseif ($sensor['poller_type'] == 'ipmi') {
            $misc_sensors[] = $sensor;
        } else {
            $sensors[] = $sensor;
        }
    }

    $snmp_data = bulk_sensor_snmpget($device, $sensors);

    $sensor_cache = sensor_precache($device, $class);

    foreach ($sensors as $sensor) {
        echo 'Checking (' . $sensor['poller_type'] . ") $class " . $sensor['sensor_descr'] . '... ' . PHP_EOL;

        if ($sensor['poller_type'] == 'snmp') {
            $mibdir = null;

            $sensor_value = trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid']] ?? ''));

            if (file_exists('includes/polling/sensors/' . $class . '/' . $device['os'] . '.inc.php')) {
                require 'includes/polling/sensors/' . $class . '/' . $device['os'] . '.inc.php';
            } elseif (isset($device['os_group']) && file_exists('includes/polling/sensors/' . $class . '/' . $device['os_group'] . '.inc.php')) {
                require 'includes/polling/sensors/' . $class . '/' . $device['os_group'] . '.inc.php';
            }

            if ($class == 'state') {
                if (! is_numeric($sensor_value)) {
                    $state_value = dbFetchCell(
                        'SELECT `state_value`
                        FROM `state_translations` LEFT JOIN `sensors_to_state_indexes`
                        ON `state_translations`.`state_index_id` = `sensors_to_state_indexes`.`state_index_id`
                        WHERE `sensors_to_state_indexes`.`sensor_id` = ?
                        AND `state_translations`.`state_descr` LIKE ?',
                        [$sensor['sensor_id'], $sensor_value]
                    );
                    d_echo('State value of ' . $sensor_value . ' is ' . $state_value . "\n");
                    if (is_numeric($state_value)) {
                        $sensor_value = $state_value;
                    }
                }
            }//end if
            unset($mib);
            unset($mibdir);
            $sensor['new_value'] = $sensor_value;
            $all_sensors[] = $sensor;
        }
    }

    foreach ($misc_sensors as $sensor) {
        if ($sensor['poller_type'] == 'agent') {
            if (isset($agent_sensors)) {
                $sensor_value = $agent_sensors[$class][$sensor['sensor_type']][$sensor['sensor_index']]['current'];
                $sensor['new_value'] = $sensor_value;
                $all_sensors[] = $sensor;
            } else {
                echo "no agent data!\n";
                continue;
            }
        } elseif ($sensor['poller_type'] == 'ipmi') {
            echo " already polled.\n";
            // ipmi should probably move here from the ipmi poller file (FIXME)
            continue;
        } else {
            echo "unknown poller type!\n";
            continue;
        }//end if
    }
    record_sensor_data($device, $all_sensors);
}//end poll_sensor()

/**
 * @param  $device
 * @param  $all_sensors
 */
function record_sensor_data($device, $all_sensors)
{
    $supported_sensors = [
        'airflow' => 'cfm',
        'ber' => '',
        'bitrate' => 'bps',
        'charge' => '%',
        'chromatic_dispersion' => 'ps/nm',
        'cooling' => 'W',
        'count' => '',
        'current' => 'A',
        'delay' => 's',
        'dbm' => 'dBm',
        'eer' => 'eer',
        'fanspeed' => 'rpm',
        'frequency' => 'Hz',
        'humidity' => '%',
        'load' => '%',
        'loss' => '%',
        'percent' => '%',
        'power' => 'W',
        'power_consumed' => 'kWh',
        'power_factor' => '',
        'pressure' => 'kPa',
        'quality_factor' => 'dB',
        'runtime' => 'Min',
        'signal' => 'dBm',
        'snr' => 'SNR',
        'state' => '#',
        'temperature' => 'C',
        'tv_signal' => 'dBmV',
        'voltage' => 'V',
        'waterflow' => 'l/m',
    ];

    foreach ($all_sensors as $sensor) {
        $class = ucfirst($sensor['sensor_class']);
        $unit = $supported_sensors[$sensor['sensor_class']];
        $sensor_value = Number::extract($sensor['new_value']);
        $prev_sensor_value = $sensor['sensor_current'];

        if ($sensor_value == -32768 || is_nan($sensor_value)) {
            echo 'Invalid (-32768 or NaN)';
            $sensor_value = 0;
        }

        if ($sensor['sensor_divisor'] && $sensor_value !== 0) {
            $sensor_value = ($sensor_value / $sensor['sensor_divisor']);
        }

        if ($sensor['sensor_multiplier']) {
            $sensor_value = ($sensor_value * $sensor['sensor_multiplier']);
        }

        if (isset($sensor['user_func'])) {
            if (is_callable($sensor['user_func'])) {
                $sensor_value = $sensor['user_func']($sensor_value);
            } else {
                $sensor_value = (new UserFuncHelper($sensor_value, $sensor['new_value'], $sensor))->{$sensor['user_func']}();
            }
        }

        $rrd_name = get_sensor_rrd_name($device, $sensor);

        $rrd_def = RrdDefinition::make()->addDataset('sensor', $sensor['rrd_type']);

        echo "$sensor_value $unit\n";

        $fields = [
            'sensor' => $sensor_value,
        ];

        $tags = [
            'sensor_class' => $sensor['sensor_class'],
            'sensor_type' => $sensor['sensor_type'],
            'sensor_descr' => $sensor['sensor_descr'],
            'sensor_index' => $sensor['sensor_index'],
            'rrd_name' => $rrd_name,
            'rrd_def' => $rrd_def,
        ];
        data_update($device, 'sensor', $tags, $fields);

        // FIXME also warn when crossing WARN level!
        if ($sensor['sensor_limit_low'] != '' && $prev_sensor_value > $sensor['sensor_limit_low'] && $sensor_value < $sensor['sensor_limit_low'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for ' . $device['hostname'] . ' ' . $sensor['sensor_descr'] . "\n";
            log_event("$class under threshold: $sensor_value $unit (< {$sensor['sensor_limit_low']} $unit)", $device, $sensor['sensor_class'], 4, $sensor['sensor_id']);
        } elseif ($sensor['sensor_limit'] != '' && $prev_sensor_value < $sensor['sensor_limit'] && $sensor_value > $sensor['sensor_limit'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for ' . $device['hostname'] . ' ' . $sensor['sensor_descr'] . "\n";
            log_event("$class above threshold: $sensor_value $unit (> {$sensor['sensor_limit']} $unit)", $device, $sensor['sensor_class'], 4, $sensor['sensor_id']);
        }
        if ($sensor['sensor_class'] == 'state' && $prev_sensor_value != $sensor_value) {
            $trans = array_column(
                dbFetchRows(
                    'SELECT `state_translations`.`state_value`, `state_translations`.`state_descr` FROM `sensors_to_state_indexes` LEFT JOIN `state_translations` USING (`state_index_id`) WHERE `sensors_to_state_indexes`.`sensor_id`=? AND `state_translations`.`state_value` IN (?,?)',
                    [$sensor['sensor_id'], $sensor_value, $prev_sensor_value]
                ),
                'state_descr',
                'state_value'
            );

            log_event("$class sensor {$sensor['sensor_descr']} has changed from {$trans[$prev_sensor_value]} ($prev_sensor_value) to {$trans[$sensor_value]} ($sensor_value)", $device, $class, 3, $sensor['sensor_id']);
        }
        if ($sensor_value != $prev_sensor_value) {
            dbUpdate(['sensor_current' => $sensor_value, 'sensor_prev' => $prev_sensor_value, 'lastupdate' => ['NOW()']], 'sensors', '`sensor_class` = ? AND `sensor_id` = ?', [$sensor['sensor_class'], $sensor['sensor_id']]);
        }
    }
}

/**
 * Update the application status and output in the database.
 *
 * Metric values should have key for of the matching name.
 * If you have multiple groups of metrics, you can group them with multiple sub arrays
 * The group name (key) will be prepended to each metric in that group, separated by an underscore
 * The special group "none" will not be prefixed.
 *
 * @param  \App\Models\Application  $app  app from the db, including app_id
 * @param  string  $response  This should be the return state of Application polling
 * @param  array  $metrics  an array of additional metrics to store in the database for alerting
 * @param  string  $status  This is the current value for alerting
 */
function update_application($app, $response, $metrics = [], $status = '')
{
    if (! $app) {
        d_echo('$app does not exist, could not update');

        return;
    }

    $app->app_state = 'UNKNOWN';
    $app->app_status = $status;
    $app->timestamp = DB::raw('NOW()');

    if ($response != '' && $response !== false) {
        // if the response indicates an error, set it and set app_status to the raw response
        if (Str::contains($response, [
            'Traceback (most recent call last):',
        ])) {
            $app->app_state = 'ERROR';
            $app->app_status = $response;
        } elseif (preg_match('/^(ERROR|LEGACY|UNSUPPORTED)/', $response, $matches)) {
            $app->app_state = $matches[1];
            $app->app_status = $response;
        } else {
            // should maybe be 'unknown' as state
            $app->app_state = 'OK';
        }
    }

    if ($app->isDirty('app_state')) {
        $app->app_state_prev = $app->getOriginal('app_state');

        switch ($app->app_state) {
            case 'OK':
                $severity = Severity::Ok;
                $event_msg = 'changed to OK';
                break;
            case 'ERROR':
                $severity = Severity::Error;
                $event_msg = 'ends with ERROR';
                break;
            case 'LEGACY':
                $severity = Severity::Warning;
                $event_msg = 'Client Agent is deprecated';
                break;
            case 'UNSUPPORTED':
                $severity = Severity::Error;
                $event_msg = 'Client Agent Version is not supported';
                break;
            default:
                $severity = Severity::Unknown;
                $event_msg = 'has UNKNOWN state';
                break;
        }
        \App\Models\Eventlog::log('Application ' . $app->displayName() . ' ' . $event_msg, DeviceCache::getPrimary(), 'application', $severity);
    }

    $app->save();

    // update metrics
    if (! empty($metrics)) {
        $db_metrics = dbFetchRows('SELECT * FROM `application_metrics` WHERE app_id=?', [$app['app_id']]);
        $db_metrics = array_by_column($db_metrics, 'metric');

        // allow two level metrics arrays, flatten them and prepend the group name
        if (is_array(current($metrics))) {
            $metrics = array_reduce(
                array_keys($metrics),
                function ($carry, $metric_group) use ($metrics) {
                    if ($metric_group == 'none') {
                        $prefix = '';
                    } else {
                        $prefix = $metric_group . '_';
                    }

                    foreach ($metrics[$metric_group] as $metric_name => $value) {
                        $carry[$prefix . $metric_name] = $value;
                    }

                    return $carry;
                },
                []
            );
        }

        echo ': ';
        foreach ($metrics as $metric_name => $value) {
            $value = (float) $value; // cast
            if (! isset($db_metrics[$metric_name])) {
                // insert new metric
                dbInsert(
                    [
                        'app_id' => $app['app_id'],
                        'metric' => $metric_name,
                        'value' => $value,
                    ],
                    'application_metrics'
                );
                echo '+';
            } elseif ($value != $db_metrics[$metric_name]['value']) {
                dbUpdate(
                    [
                        'value' => $value,
                        'value_prev' => $db_metrics[$metric_name]['value'],
                    ],
                    'application_metrics',
                    'app_id=? && metric=?',
                    [$app['app_id'], $metric_name]
                );
                echo 'U';
            } else {
                echo '.';
            }

            unset($db_metrics[$metric_name]);
        }

        // remove no longer existing metrics (generally should not happen
        foreach ($db_metrics as $db_metric) {
            dbDelete(
                'application_metrics',
                'app_id=? && metric=?',
                [$app['app_id'], $db_metric['metric']]
            );
            echo '-';
        }

        echo PHP_EOL;
    }
}

/**
 * This is to make it easier polling apps. Also to help standardize around JSON.
 *
 * If the data has is in base64, it will be converted and then gunzipped.
 * https://github.com/librenms/librenms-agent/blob/master/utils/lnms_return_optimizer
 * May be used to convert output from extends to that via piping it through it.
 *
 * The required keys for the returned JSON are as below.
 *  version     - The version of the snmp extend script. Should be numeric and at least 1.
 *  error       - Error code from the snmp extend script. Should be > 0 (0 will be ignored and negatives are reserved)
 *  errorString - Text to describe the error.
 *  data        - An key with an array with the data to be used.
 *
 * If the app returns an error, an exception will be raised.
 * Positive numbers will be errors returned by the extend script.
 *
 * Possible parsing related errors:
 * -2 : Failed to fetch data from the device
 * -3 : Could not decode the JSON.
 * -4 : Empty JSON parsed, meaning blank JSON was returned.
 * -5 : Valid json, but missing required keys
 * -6 : Returned version is less than the min version.
 * -7 : Base64 decode failure.
 * -8 : Gzip decode failure.
 *
 * Error checking may also be done via checking the exceptions listed below.
 *   JsonAppPollingFailedException, -2        : Empty return from SNMP.
 *   JsonAppParsingFailedException, -3        : Could not parse the JSON.
 *   JsonAppBlankJsonException, -4            : Blank JSON.
 *   JsonAppMissingKeysException, -5          : Missing required keys.
 *   JsonAppWrongVersionException , -6        : Older version than supported.
 *   JsonAppExtendErroredException            : Polling and parsing was good, but the returned data has an error set.
 *                                              This may be checked via $e->getParsedJson() and then checking the
 *                                              keys error and errorString.
 *   JsonAppPollingBase64DecodeException , -7 : Base64 decoding failed.
 *   JsonAppPollingGzipDecodeException , -8   : Gzip decoding failed.
 * The error value can be accessed via $e->getCode()
 * The output can be accessed via $->getOutput() Only returned for code -3 or lower.
 * The parsed JSON can be access via $e->getParsedJson()
 *
 * All of the exceptions extend JsonAppException.
 *
 * If the error is less than -1, you can assume it is a legacy snmp extend script.
 *
 * @param  array  $device
 * @param  string  $extend  the extend name. For example, if 'zfs' is passed it will be converted to 'nsExtendOutputFull.3.122.102.115'.
 * @param  int  $min_version  the minimum version to accept for the returned JSON. default: 1
 * @return array The json output data parsed into an array
 *
 * @throws JsonAppBlankJsonException
 * @throws JsonAppExtendErroredException
 * @throws JsonAppMissingKeysException
 * @throws JsonAppParsingFailedException
 * @throws JsonAppPollingFailedException
 * @throws JsonAppWrongVersionException
 */
function json_app_get($device, $extend, $min_version = 1)
{
    $output = snmp_get($device, 'nsExtendOutputFull.' . \LibreNMS\Util\Oid::ofString($extend), '-Oqv', 'NET-SNMP-EXTEND-MIB');

    // save for returning if not JSON
    $orig_output = $output;

    // make sure we actually get something back
    if (empty($output)) {
        throw new JsonAppPollingFailedException('Empty return from snmp_get.', -2);
    }

    // checks for base64 decoding and converts it to non-base64 so it can gunzip
    if (preg_match('/^[A-Za-z0-9\/\+\n]+\=*\n*$/', $output) && ! preg_match('/^[0-9]+\n/', $output)) {
        $output = base64_decode($output);
        if (! $output) {
            if (Debug::isEnabled()) {
                echo "Decoding Base64 Failed...\n\n";
            }
            throw new JsonAppBase64DecodeException('Base64 decode failed.', $orig_output, -7);
        }
        $output = gzdecode($output);
        if (! $output) {
            if (Debug::isEnabled()) {
                echo "Decoding GZip failed...\n\n";
            }
            throw new JsonAppGzipDecodeException('Gzip decode failed.', $orig_output, -8);
        }
        if (Debug::isVerbose()) {
            echo 'Decoded Base64+GZip Output: ' . $output . "\n\n";
        }
    }

    //  turn the JSON into a array
    $parsed_json = json_decode(stripslashes($output), true);

    // improper JSON or something else was returned. Populate the variable with an error.
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new JsonAppParsingFailedException('Invalid JSON', $orig_output, -3);
    }

    // There no keys in the array, meaning '{}' was was returned
    if (empty($parsed_json)) {
        throw new JsonAppBlankJsonException('Blank JSON returned.', $output, -4);
    }

    // It is a legacy JSON app extend, meaning these are not set
    if (! isset($parsed_json['error'], $parsed_json['data'], $parsed_json['errorString'], $parsed_json['version'])) {
        throw new JsonAppMissingKeysException('Legacy script or extend error, missing one or more required keys.', $output, $parsed_json, -5);
    }

    if ($parsed_json['version'] < $min_version) {
        throw new JsonAppWrongVersionException("Script,'" . $parsed_json['version'] . "', older than required version of '$min_version'", $output, $parsed_json, -6);
    }

    if ($parsed_json['error'] != 0) {
        throw new JsonAppExtendErroredException("Script returned exception: {$parsed_json['errorString']}", $output, $parsed_json, $parsed_json['error']);
    }

    return $parsed_json;
}

/**
 * Some data arrays returned with json_app_get are deeper than
 * update_application likes. This recurses through the array
 * and flattens it out so it can nicely be inserted into the
 * database.
 *
 * One argument is taken and that is the array to flatten.
 *
 * @param  array  $array
 * @param  string  $prefix  What to prefix to the name. Defaults to '', nothing.
 * @param  string  $joiner  The string to join the prefix, if set to something other
 *                          than '', and array keys with.
 * @return array The flattened array.
 */
function data_flatten($array, $prefix = '', $joiner = '_')
{
    $return = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (strcmp($prefix, '')) {
                $key = $prefix . $joiner . $key;
            }
            $return = array_merge($return, data_flatten($value, $key, $joiner));
        } else {
            if (strcmp($prefix, '')) {
                $key = $prefix . $joiner . $key;
            }
            $return[$key] = $value;
        }
    }

    return $return;
}
