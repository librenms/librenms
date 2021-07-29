<?php

use App\Models\DeviceGraph;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\Alert;
use LibreNMS\Exceptions\JsonAppBlankJsonException;
use LibreNMS\Exceptions\JsonAppExtendErroredException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\Exceptions\JsonAppPollingFailedException;
use LibreNMS\Exceptions\JsonAppWrongVersionException;
use LibreNMS\RRD\RrdDefinition;

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
        $multi_response = snmp_get_multi_oid($device, $oids, '-OUQnte');
        $cache = array_merge($cache, $multi_response);
    }

    return $cache;
}

/**
 * @param $device
 * @param string $type type/class of sensor
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

            $sensor_value = trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid']]));

            if (file_exists('includes/polling/sensors/' . $class . '/' . $device['os'] . '.inc.php')) {
                require 'includes/polling/sensors/' . $class . '/' . $device['os'] . '.inc.php';
            } elseif (file_exists('includes/polling/sensors/' . $class . '/' . $device['os_group'] . '.inc.php')) {
                require 'includes/polling/sensors/' . $class . '/' . $device['os_group'] . '.inc.php';
            }

            if ($class == 'temperature') {
                preg_match('/[\d\.\-]+/', $sensor_value, $temp_response);
                if (! empty($temp_response[0])) {
                    $sensor_value = $temp_response[0];
                }
            } elseif ($class == 'state') {
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
 * @param $device
 * @param $all_sensors
 */
function record_sensor_data($device, $all_sensors)
{
    $supported_sensors = [
        'current'     => 'A',
        'frequency'   => 'Hz',
        'runtime'     => 'Min',
        'humidity'    => '%',
        'fanspeed'    => 'rpm',
        'power'       => 'W',
        'voltage'     => 'V',
        'temperature' => 'C',
        'dbm'         => 'dBm',
        'charge'      => '%',
        'load'        => '%',
        'state'       => '#',
        'signal'      => 'dBm',
        'airflow'     => 'cfm',
        'snr'         => 'SNR',
        'pressure'    => 'kPa',
        'cooling'     => 'W',
    ];

    foreach ($all_sensors as $sensor) {
        $class = ucfirst($sensor['sensor_class']);
        $unit = $supported_sensors[$sensor['sensor_class']];
        $sensor_value = cast_number($sensor['new_value']);
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

        if (isset($sensor['user_func']) && is_callable($sensor['user_func'])) {
            $sensor_value = $sensor['user_func']($sensor_value);
        }

        $rrd_name = get_sensor_rrd_name($device, $sensor);

        $rrd_def = RrdDefinition::make()->addDataset('sensor', 'GAUGE');

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
 * @param array $device The device to poll
 * @param bool $force_module Ignore device module overrides
 * @return bool
 */
function poll_device($device, $force_module = false)
{
    global $device, $graphs;

    $device_start = microtime(true);

    $attribs = DeviceCache::getPrimary()->getAttribs();
    $device['attribs'] = $attribs;

    load_os($device);
    $os = \LibreNMS\OS::make($device);

    unset($array);

    // Start counting device poll time
    echo 'Hostname:    ' . $device['hostname'] . PHP_EOL;
    echo 'Device ID:   ' . $device['device_id'] . PHP_EOL;
    echo 'OS:          ' . $device['os'] . PHP_EOL;

    if (empty($device['overwrite_ip'])) {
        $ip = dnslookup($device);
    } else {
        $ip = $device['overwrite_ip'];
    }

    $db_ip = null;
    if (! empty($ip)) {
        if (empty($device['overwrite_ip'])) {
            echo 'Resolved IP: ' . $ip . PHP_EOL;
        } else {
            echo 'Assigned IP: ' . $ip . PHP_EOL;
        }
        $db_ip = inet_pton($ip);
    }

    if (! empty($db_ip) && inet6_ntop($db_ip) != inet6_ntop($device['ip'])) {
        log_event('Device IP changed to ' . $ip, $device, 'system', 3);
        dbUpdate(['ip' => $db_ip], 'devices', 'device_id=?', [$device['device_id']]);
    }

    if ($os_group = Config::get("os.{$device['os']}.group")) {
        $device['os_group'] = $os_group;
        echo ' (' . $device['os_group'] . ')';
    }

    echo PHP_EOL . PHP_EOL;

    unset($poll_update);
    unset($poll_update_query);
    unset($poll_separator);
    $poll_update_array = [];
    $update_array = [];

    $host_rrd = Rrd::name($device['hostname'], '', '');
    if (Config::get('norrd') !== true && ! is_dir($host_rrd)) {
        mkdir($host_rrd);
        echo "Created directory : $host_rrd\n";
    }

    $response = device_is_up($device, true);

    if ($response['status'] == '1') {
        if ($device['snmp_disable']) {
            Config::set('poller_modules', ['availability' => true]);
        } else {
            // we always want the core module to be included, prepend it
            Config::set('poller_modules', ['core' => true, 'availability' => true] + Config::get('poller_modules'));
        }

        printChangedStats(true); // don't count previous stats
        foreach (Config::get('poller_modules') as $module => $module_status) {
            $os_module_status = Config::get("os.{$device['os']}.poller_modules.$module");
            d_echo('Modules status: Global' . (isset($module_status) ? ($module_status ? '+ ' : '- ') : '  '));
            d_echo('OS' . (isset($os_module_status) ? ($os_module_status ? '+ ' : '- ') : '  '));
            d_echo('Device' . (isset($attribs['poll_' . $module]) ? ($attribs['poll_' . $module] ? '+ ' : '- ') : '  '));
            if ($force_module === true ||
                $attribs['poll_' . $module] ||
                ($os_module_status && ! isset($attribs['poll_' . $module])) ||
                ($module_status && ! isset($os_module_status) && ! isset($attribs['poll_' . $module]))) {
                $start_memory = memory_get_usage();
                $module_start = microtime(true);
                echo "\n#### Load poller module $module ####\n";

                try {
                    include "includes/polling/$module.inc.php";
                } catch (Exception $e) {
                    // isolate module exceptions so they don't disrupt the polling process
                    echo $e->getTraceAsString() . PHP_EOL;
                    c_echo("%rError in $module module.%n " . $e->getMessage() . PHP_EOL);
                    logfile("Error in $module module. " . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
                }

                $module_time = microtime(true) - $module_start;
                $module_mem = (memory_get_usage() - $start_memory);
                printf("\n>> Runtime for poller module '%s': %.4f seconds with %s bytes\n", $module, $module_time, $module_mem);
                printChangedStats();
                echo "#### Unload poller module $module ####\n\n";

                // save per-module poller stats
                $tags = [
                    'module'      => $module,
                    'rrd_def'     => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
                    'rrd_name'    => ['poller-perf', $module],
                ];
                $fields = [
                    'poller' => $module_time,
                ];
                data_update($device, 'poller-perf', $tags, $fields);
                $os->enableGraph('poller_perf');

                // remove old rrd
                $oldrrd = Rrd::name($device['hostname'], ['poller', $module, 'perf']);
                if (is_file($oldrrd)) {
                    unlink($oldrrd);
                }
                unset($tags, $fields, $oldrrd);
            } elseif (isset($attribs['poll_' . $module]) && $attribs['poll_' . $module] == '0') {
                echo "Module [ $module ] disabled on host.\n\n";
            } elseif (isset($os_module_status) && $os_module_status == '0') {
                echo "Module [ $module ] disabled on os.\n\n";
            } else {
                echo "Module [ $module ] disabled globally.\n\n";
            }
        }

        // Ping response
        if (can_ping_device($attribs) === true && ! empty($response['ping_time'])) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('ping', 'GAUGE', 0, 65535),
            ];
            $fields = [
                'ping' => $response['ping_time'],
            ];

            $update_array['last_ping'] = ['NOW()'];
            $update_array['last_ping_timetaken'] = $response['ping_time'];

            data_update($device, 'ping-perf', $tags, $fields);
            $os->enableGraph('ping_perf');
        }

        $device_time = round(microtime(true) - $device_start, 3);

        // Poller performance
        if (! empty($device_time)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
                'module'  => 'ALL',
            ];
            $fields = [
                'poller' => $device_time,
            ];

            data_update($device, 'poller-perf', $tags, $fields);
            $os->enableGraph('poller_modules_perf');
        }

        if (! $force_module) {
            // don't update last_polled time if we are forcing a specific module to be polled
            $update_array['last_polled'] = ['NOW()'];
            $update_array['last_polled_timetaken'] = $device_time;

            echo 'Enabling graphs: ';
            DeviceGraph::deleted(function ($graph) {
                echo '-';
            });
            DeviceGraph::created(function ($graph) {
                echo '+';
            });

            $os->persistGraphs();
            echo PHP_EOL;
        }

        $updated = dbUpdate($update_array, 'devices', '`device_id` = ?', [$device['device_id']]);
        if ($updated) {
            d_echo('Updating ' . $device['hostname'] . PHP_EOL);
        }

        echo "\nPolled in $device_time seconds\n";

        // check if the poll took to long and log an event
        if ($device_time > Config::get('rrd.step')) {
            log_event('Polling took longer than ' . round(Config::get('rrd.step') / 60, 2) .
                ' minutes!  This will cause gaps in graphs.', $device, 'system', 5);
        }

        unset($storage_cache);
        // Clear cache of hrStorage ** MAYBE FIXME? **
        unset($cache);
        // Clear cache (unify all things here?)

        return true; // device was polled
    }

    return false; // device not polled
}//end poll_device()

/**
 * Update the application status and output in the database.
 *
 * Metric values should have key for of the matching name.
 * If you have multiple groups of metrics, you can group them with multiple sub arrays
 * The group name (key) will be prepended to each metric in that group, separated by an underscore
 * The special group "none" will not be prefixed.
 *
 * @param array $app app from the db, including app_id
 * @param string $response This should be the return state of Application polling
 * @param array $metrics an array of additional metrics to store in the database for alerting
 * @param string $status This is the current value for alerting
 */
function update_application($app, $response, $metrics = [], $status = '')
{
    if (! is_numeric($app['app_id'])) {
        d_echo('$app does not contain app_id, could not update');

        return;
    }

    $data = [
        'app_state'  => 'UNKNOWN',
        'app_status' => $status,
        'timestamp'  => ['NOW()'],
    ];

    if ($response != '' && $response !== false) {
        if (Str::contains($response, [
            'Traceback (most recent call last):',
        ])) {
            $data['app_state'] = 'ERROR';
        } elseif (in_array($response, ['OK', 'ERROR', 'LEGACY', 'UNSUPPORTED'])) {
            $data['app_state'] = $response;
        } else {
            // should maybe be 'unknown' as state
            $data['app_state'] = 'OK';
        }
    }

    if ($data['app_state'] != $app['app_state']) {
        $data['app_state_prev'] = $app['app_state'];

        $device = dbFetchRow('SELECT * FROM devices LEFT JOIN applications ON devices.device_id=applications.device_id WHERE applications.app_id=?', [$app['app_id']]);

        $app_name = \LibreNMS\Util\StringHelpers::nicecase($app['app_type']);

        switch ($data['app_state']) {
            case 'OK':
                $severity = Alert::OK;
                $event_msg = 'changed to OK';
                break;
            case 'ERROR':
                $severity = Alert::ERROR;
                $event_msg = 'ends with ERROR';
                break;
            case 'LEGACY':
                $severity = Alert::WARNING;
                $event_msg = 'Client Agent is deprecated';
                break;
            case 'UNSUPPORTED':
                $severity = Alert::ERROR;
                $event_msg = 'Client Agent Version is not supported';
                break;
            default:
                $severity = Alert::UNKNOWN;
                $event_msg = 'has UNKNOWN state';
                break;
        }
        log_event('Application ' . $app_name . ' ' . $event_msg, $device, 'application', $severity);
    }
    dbUpdate($data, 'applications', '`app_id` = ?', [$app['app_id']]);

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
 *
 * Error checking may also be done via checking the exceptions listed below.
 *   JsonAppPollingFailedException, -2 : Empty return from SNMP.
 *   JsonAppParsingFailedException, -3 : Could not parse the JSON.
 *   JsonAppBlankJsonException, -4     : Blank JSON.
 *   JsonAppMissingKeysException, -5   : Missing required keys.
 *   JsonAppWrongVersionException , -6 : Older version than supported.
 *   JsonAppExtendErroredException     : Polling and parsing was good, but the returned data has an error set.
 *                                       This may be checked via $e->getParsedJson() and then checking the
 *                                       keys error and errorString.
 * The error value can be accessed via $e->getCode()
 * The output can be accessed via $->getOutput() Only returned for code -3 or lower.
 * The parsed JSON can be access via $e->getParsedJson()
 *
 * All of the exceptions extend JsonAppException.
 *
 * If the error is less than -1, you can assume it is a legacy snmp extend script.
 *
 * @param array $device
 * @param string $extend the extend name. For example, if 'zfs' is passed it will be converted to 'nsExtendOutputFull.3.122.102.115'.
 * @param int $min_version the minimum version to accept for the returned JSON. default: 1
 *
 * @return array The json output data parsed into an array
 * @throws JsonAppBlankJsonException
 * @throws JsonAppExtendErroredException
 * @throws JsonAppMissingKeysException
 * @throws JsonAppParsingFailedException
 * @throws JsonAppPollingFailedException
 * @throws JsonAppWrongVersionException
 */
function json_app_get($device, $extend, $min_version = 1)
{
    $output = snmp_get($device, 'nsExtendOutputFull.' . string_to_oid($extend), '-Oqv', 'NET-SNMP-EXTEND-MIB');

    // make sure we actually get something back
    if (empty($output)) {
        throw new JsonAppPollingFailedException('Empty return from snmp_get.', -2);
    }

    //  turn the JSON into a array
    $parsed_json = json_decode(stripslashes($output), true);

    // improper JSON or something else was returned. Populate the variable with an error.
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new JsonAppParsingFailedException('Invalid JSON', $output, -3);
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
 * @param array $array
 * @param string $prefix What to prefix to the name. Defaults to '', nothing.
 * @param string $joiner The string to join the prefix, if set to something other
 *                       than '', and array keys with.
 *
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
