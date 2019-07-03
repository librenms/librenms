<?php

use LibreNMS\Config;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppPollingFailedException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\Exceptions\JsonAppBlankJsonException;
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppWrongVersionException;
use LibreNMS\Exceptions\JsonAppExtendErroredException;

function bulk_sensor_snmpget($device, $sensors)
{
    $oid_per_pdu = get_device_oid_limit($device);
    $sensors = array_chunk($sensors, $oid_per_pdu);
    $cache = array();
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
    $sensor_cache = array();
    if (file_exists('includes/polling/sensors/pre-cache/'. $device['os'] .'.inc.php')) {
        include 'includes/polling/sensors/pre-cache/'. $device['os'] .'.inc.php';
    }
    return $sensor_cache;
}

function poll_sensor($device, $class)
{
    global $agent_sensors;

    $sensors = array();
    $misc_sensors = array();
    $all_sensors = array();

    foreach (dbFetchRows("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ?", array($class, $device['device_id'])) as $sensor) {
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
        echo 'Checking (' . $sensor['poller_type'] . ") $class " . $sensor['sensor_descr'] . '... '.PHP_EOL;

        if ($sensor['poller_type'] == 'snmp') {
            $mibdir = null;

            $sensor_value = trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid']]));

            if (file_exists('includes/polling/sensors/'. $class .'/'. $device['os'] .'.inc.php')) {
                require 'includes/polling/sensors/'. $class .'/'. $device['os'] .'.inc.php';
            } elseif (file_exists('includes/polling/sensors/'. $class .'/'. $device['os_group'] .'.inc.php')) {
                require 'includes/polling/sensors/'. $class .'/'. $device['os_group'] .'.inc.php';
            }

            if ($class == 'temperature') {
                preg_match('/[\d\.\-]+/', $sensor_value, $temp_response);
                if (!empty($temp_response[0])) {
                    $sensor_value = $temp_response[0];
                }
            } elseif ($class == 'state') {
                if (!is_numeric($sensor_value)) {
                    $state_value = dbFetchCell(
                        'SELECT `state_value`
                        FROM `state_translations` LEFT JOIN `sensors_to_state_indexes`
                        ON `state_translations`.`state_index_id` = `sensors_to_state_indexes`.`state_index_id`
                        WHERE `sensors_to_state_indexes`.`sensor_id` = ?
                        AND `state_translations`.`state_descr` LIKE ?',
                        array($sensor['sensor_id'], $sensor_value)
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
    $supported_sensors = array(
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
    );

    foreach ($all_sensors as $sensor) {
        $class             = ucfirst($sensor['sensor_class']);
        $unit              = $supported_sensors[$class];
        $sensor_value      = $sensor['new_value'];
        $prev_sensor_value = $sensor['sensor_current'];

        if ($sensor_value == -32768) {
            echo 'Invalid (-32768) ';
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

        $fields = array(
            'sensor' => $sensor_value,
        );

        $tags = array(
            'sensor_class' => $sensor['sensor_class'],
            'sensor_type' => $sensor['sensor_type'],
            'sensor_descr' => $sensor['sensor_descr'],
            'sensor_index' => $sensor['sensor_index'],
            'rrd_name' => $rrd_name,
            'rrd_def' => $rrd_def
        );
        data_update($device, 'sensor', $tags, $fields);

        // FIXME also warn when crossing WARN level!
        if ($sensor['sensor_limit_low'] != '' && $prev_sensor_value > $sensor['sensor_limit_low'] && $sensor_value < $sensor['sensor_limit_low'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for '.$device['hostname'].' '.$sensor['sensor_descr']."\n";
            log_event("$class {$sensor['sensor_descr']} under threshold: $sensor_value $unit (< {$sensor['sensor_limit_low']} $unit)", $device, $class, 4, $sensor['sensor_id']);
        } elseif ($sensor['sensor_limit'] != '' && $prev_sensor_value < $sensor['sensor_limit'] && $sensor_value > $sensor['sensor_limit'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for '.$device['hostname'].' '.$sensor['sensor_descr']."\n";
            log_event("$class {$sensor['sensor_descr']} above threshold: $sensor_value $unit (> {$sensor['sensor_limit']} $unit)", $device, $class, 4, $sensor['sensor_id']);
        }
        if ($sensor['sensor_class'] == 'state' && $prev_sensor_value != $sensor_value) {
            $trans = array_column(
                dbFetchRows(
                    "SELECT `state_translations`.`state_value`, `state_translations`.`state_descr` FROM `sensors_to_state_indexes` LEFT JOIN `state_translations` USING (`state_index_id`) WHERE `sensors_to_state_indexes`.`sensor_id`=? AND `state_translations`.`state_value` IN (?,?)",
                    [$sensor['sensor_id'], $sensor_value, $prev_sensor_value]
                ),
                'state_descr',
                'state_value'
            );

            log_event("$class sensor {$sensor['sensor_descr']} has changed from {$trans[$prev_sensor_value]} ($prev_sensor_value) to {$trans[$sensor_value]} ($sensor_value)", $device, $class, 3, $sensor['sensor_id']);
        }
        if ($sensor_value != $prev_sensor_value) {
            dbUpdate(array('sensor_current' => $sensor_value, 'sensor_prev' => $prev_sensor_value, 'lastupdate' => array('NOW()')), 'sensors', "`sensor_class` = ? AND `sensor_id` = ?", array($class, $sensor['sensor_id']));
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
    global $device;

    $device_start = microtime(true);

    $attribs = get_dev_attribs($device['device_id']);
    $device['attribs'] = $attribs;

    load_os($device);

    $device['snmp_max_repeaters'] = $attribs['snmp_max_repeaters'];
    $device['snmp_max_oid'] = $attribs['snmp_max_oid'];

    unset($array);

    // Start counting device poll time
    echo 'Hostname:    ' . $device['hostname'] . PHP_EOL;
    echo 'Device ID:   ' . $device['device_id'] . PHP_EOL;
    echo 'OS:          ' . $device['os'] . PHP_EOL;
    $ip = dnslookup($device);

    $db_ip = null;
    if (!empty($ip)) {
        echo 'Resolved IP: '.$ip.PHP_EOL;
        $db_ip = inet_pton($ip);
    }

    if (!empty($db_ip) && inet6_ntop($db_ip) != inet6_ntop($device['ip'])) {
        log_event('Device IP changed to ' . $ip, $device, 'system', 3);
        dbUpdate(array('ip' => $db_ip), 'devices', 'device_id=?', array($device['device_id']));
    }

    if ($os_group = Config::get("os.{$device['os']}.group")) {
        $device['os_group'] = $os_group;
        echo ' ('.$device['os_group'].')';
    }

    echo PHP_EOL.PHP_EOL;

    unset($poll_update);
    unset($poll_update_query);
    unset($poll_separator);
    $poll_update_array = array();
    $update_array = array();

    $host_rrd = rrd_name($device['hostname'], '', '');
    if (Config::get('norrd') !== true && !is_dir($host_rrd)) {
        mkdir($host_rrd);
        echo "Created directory : $host_rrd\n";
    }

    $response = device_is_up($device, true);

    if ($response['status'] == '1') {
        $graphs    = array();
        $oldgraphs = array();

        if ($device['snmp_disable']) {
            Config::set('poller_modules', []);
        } else {
            // we always want the core module to be included, prepend it
            Config::set('poller_modules', ['core' => true] + Config::get('poller_modules'));
        }

        printChangedStats(true); // don't count previous stats
        foreach (Config::get('poller_modules') as $module => $module_status) {
            $os_module_status = Config::get("os.{$device['os']}.poller_modules.$module");
            d_echo("Modules status: Global" . (isset($module_status) ? ($module_status ? '+ ' : '- ') : '  '));
            d_echo("OS" . (isset($os_module_status) ? ($os_module_status ? '+ ' : '- ') : '  '));
            d_echo("Device" . (isset($attribs['poll_' . $module]) ? ($attribs['poll_' . $module] ? '+ ' : '- ') : '  '));
            if ($force_module === true ||
                $attribs['poll_'.$module] ||
                ($os_module_status && !isset($attribs['poll_'.$module])) ||
                ($module_status && !isset($os_module_status) && !isset($attribs['poll_' . $module]))) {
                $start_memory = memory_get_usage();
                $module_start = microtime(true);
                echo "\n#### Load poller module $module ####\n";

                try {
                    include "includes/polling/$module.inc.php";
                } catch (Exception $e) {
                    // isolate module exceptions so they don't disrupt the polling process
                    echo $e->getTraceAsString() .PHP_EOL;
                    c_echo("%rError in $module module.%n " . $e->getMessage() . PHP_EOL);
                    logfile("Error in $module module. " . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
                }

                $module_time = microtime(true) - $module_start;
                $module_mem  = (memory_get_usage() - $start_memory);
                printf("\n>> Runtime for poller module '%s': %.4f seconds with %s bytes\n", $module, $module_time, $module_mem);
                printChangedStats();
                echo "#### Unload poller module $module ####\n\n";

                // save per-module poller stats
                $tags = array(
                    'module'      => $module,
                    'rrd_def'     => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
                    'rrd_name'    => array('poller-perf', $module),
                );
                $fields = array(
                    'poller' => $module_time,
                );
                data_update($device, 'poller-perf', $tags, $fields);

                // remove old rrd
                $oldrrd = rrd_name($device['hostname'], array('poller', $module, 'perf'));
                if (is_file($oldrrd)) {
                    unlink($oldrrd);
                }
                unset($tags, $fields, $oldrrd);
            } elseif (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] == '0') {
                echo "Module [ $module ] disabled on host.\n\n";
            } elseif (isset($os_module_status) && $os_module_status == '0') {
                echo "Module [ $module ] disabled on os.\n\n";
            } else {
                echo "Module [ $module ] disabled globally.\n\n";
            }
        }

        // Update device_groups
        echo "### Start Device Groups ###\n";
        $dg_start = microtime(true);

        $group_changes = \App\Models\DeviceGroup::updateGroupsFor($device['device_id']);
        d_echo("Groups Added: " . implode(',', $group_changes['attached']) . PHP_EOL);
        d_echo("Groups Removed: " . implode(',', $group_changes['detached']) . PHP_EOL);

        echo "### End Device Groups, runtime: " . round(microtime(true) - $dg_start, 4) . "s ### \n\n";

        if (!$force_module && !empty($graphs)) {
            echo "Enabling graphs: ";
            // FIXME EVENTLOGGING -- MAKE IT SO WE DO THIS PER-MODULE?
            // This code cycles through the graphs already known in the database and the ones we've defined as being polled here
            // If there any don't match, they're added/deleted from the database.
            // Ideally we should hold graphs for xx days/weeks/polls so that we don't needlessly hide information.
            foreach (dbFetch('SELECT `graph` FROM `device_graphs` WHERE `device_id` = ?', array($device['device_id'])) as $graph) {
                if (isset($graphs[$graph['graph']])) {
                    $oldgraphs[$graph['graph']] = true;
                } else {
                    dbDelete('device_graphs', '`device_id` = ? AND `graph` = ?', array($device['device_id'], $graph['graph']));
                }
            }

            foreach ($graphs as $graph => $value) {
                if (!isset($oldgraphs[$graph])) {
                    echo '+';
                    dbInsert(array('device_id' => $device['device_id'], 'graph' => $graph), 'device_graphs');
                }

                echo $graph.' ';
            }
            echo PHP_EOL;
        }//end if

        // Ping response
        if (can_ping_device($attribs) === true  &&  !empty($response['ping_time'])) {
            $tags = array(
                'rrd_def' => RrdDefinition::make()->addDataset('ping', 'GAUGE', 0, 65535),
            );
            $fields = array(
                'ping' => $response['ping_time'],
            );

            $update_array['last_ping']             = array('NOW()');
            $update_array['last_ping_timetaken']   = $response['ping_time'];

            data_update($device, 'ping-perf', $tags, $fields);
        }

        $device_time  = round(microtime(true) - $device_start, 3);

        // Poller performance
        if (!empty($device_time)) {
            $tags = array(
                'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
                'module'  => 'ALL',
            );
            $fields = array(
                'poller' => $device_time,
            );

            data_update($device, 'poller-perf', $tags, $fields);
        }

        if (!$force_module) {
            // don't update last_polled time if we are forcing a specific module to be polled
            $update_array['last_polled']           = array('NOW()');
            $update_array['last_polled_timetaken'] = $device_time;
        }

        $updated = dbUpdate($update_array, 'devices', '`device_id` = ?', array($device['device_id']));
        if ($updated) {
            d_echo('Updating ' . $device['hostname'] . PHP_EOL);
        }

        echo "\nPolled in $device_time seconds\n";

        // check if the poll took to long and log an event
        if ($device_time > Config::get('rrd.step')) {
            log_event("Polling took longer than " . round(Config::get('rrd.step') / 60, 2) .
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
 * if no rrd_name parameter is passed, the MIB name is used as the rrd_file_name
 */
function poll_mib_def($device, $mib_name_table, $mib_subdir, $mib_oids, $mib_graphs, &$graphs, $rrd_name = null)
{
    echo "This is poll_mib_def Processing\n";
    $mib = null;

    list($mib, $file) = explode(':', $mib_name_table, 2);

    if (is_null($rrd_name)) {
        if (str_i_contains($mib_name_table, 'UBNT')) {
            $rrd_name = strtolower($mib);
        } else {
            $rrd_name = strtolower($file);
        }
    }

    $rrd_def = new RrdDefinition();
    $oidglist  = array();
    $oidnamelist = array();
    foreach ($mib_oids as $oid => $param) {
        $oidindex  = $param[0];
        $oiddsname = $param[1];
        $oiddsdesc = $param[2];
        $oiddstype = $param[3];
        $oiddsopts = $param[4];

        if (empty($oiddsopts)) {
            $rrd_def->addDataset($oiddsname, $oiddstype, null, 100000000000);
        } else {
            $min = array_key_exists('min', $oiddsopts) ? $oiddsopts['min'] : null;
            $max = array_key_exists('max', $oiddsopts) ? $oiddsopts['max'] : null;
            $heartbeat = array_key_exists('heartbeat', $oiddsopts) ? $oiddsopts['heartbeat'] : null;
            $rrd_def->addDataset($oiddsname, $oiddstype, $min, $max, $heartbeat);
        }

        if ($oidindex != '') {
            $fulloid = $oid.'.'.$oidindex;
        } else {
            $fulloid = $oid;
        }

        // Add to oid GET list
        $oidglist[] = $fulloid;
        $oidnamelist[] = $oiddsname;
    }//end foreach

    // Implde for LibreNMS Version
    $oidilist = implode(' ', $oidglist);

    $snmpdata = snmp_get_multi($device, $oidilist, '-OQUs', $mib);
    if (isset($GLOBALS['exec_status']['exitcode']) && $GLOBALS['exec_status']['exitcode'] !== 0) {
        print_debug('  ERROR, bad snmp response');
        return false;
    }

    $oid_count = 0;
    $fields = array();
    foreach ($oidglist as $fulloid) {
        list($splitoid, $splitindex) = explode('.', $fulloid, 2);
        $val = $snmpdata[$splitindex][$splitoid];
        if (is_numeric($val)) {
            $fields[$oidnamelist[$oid_count]] = $val;
        } elseif (preg_match("/^\"(.*)\"$/", $val, $number) && is_numeric($number[1])) {
            $fields[$oidnamelist[$oid_count]] = $number[1];
        } else {
            $fields[$oidnamelist[$oid_count]] = 'U';
        }
        $oid_count++;
    }

    $tags = compact('rrd_def');
    data_update($device, $rrd_name, $tags, $fields);

    foreach ($mib_graphs as $graphtoenable) {
        $graphs[$graphtoenable] = true;
    }

    return true;
}//end poll_mib_def()


function get_main_serial($device)
{
    if ($device['os_group'] == 'cisco') {
        $serial_output = snmp_get_multi($device, ['entPhysicalSerialNum.1', 'entPhysicalSerialNum.1001'], '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');
        if (!empty($serial_output[1]['entPhysicalSerialNum'])) {
            return $serial_output[1]['entPhysicalSerialNum'];
        } elseif (!empty($serial_output[1000]['entPhysicalSerialNum'])) {
            return $serial_output[1000]['entPhysicalSerialNum'];
        } elseif (!empty($serial_output[1001]['entPhysicalSerialNum'])) {
            return $serial_output[1001]['entPhysicalSerialNum'];
        }
    }
}//end get_main_serial()

/**
 * Update the application status and output in the database.
 *
 * Metric values should have key for of the matching name.
 * If you have multiple groups of metrics, you can group them with multiple sub arrays
 * The group name (key) will be prepended to each metric in that group, separated by an underscore
 * The special group "none" will not be prefixed.
 *
 * @param array $app app from the db, including app_id
 * @param string $response This should be the full output
 * @param array $metrics an array of additional metrics to store in the database for alerting
 * @param string $status This is the current value for alerting
 */
function update_application($app, $response, $metrics = array(), $status = '')
{
    if (!is_numeric($app['app_id'])) {
        d_echo('$app does not contain app_id, could not update');
        return;
    }

    $data = array(
        'app_state'  => 'UNKNOWN',
        'app_status' => $status,
        'timestamp'  => array('NOW()'),
    );

    if ($response != '' && $response !== false) {
        if (str_contains($response, array(
            'Traceback (most recent call last):',
        ))) {
            $data['app_state'] = 'ERROR';
        } else {
            $data['app_state'] = 'OK';
        }
    }

    if ($data['app_state'] != $app['app_state']) {
        $data['app_state_prev'] = $app['app_state'];
    }
    dbUpdate($data, 'applications', '`app_id` = ?', array($app['app_id']));

    // update metrics
    if (!empty($metrics)) {
        $db_metrics = dbFetchRows('SELECT * FROM `application_metrics` WHERE app_id=?', array($app['app_id']));
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
                array()
            );
        }

        echo ': ';
        foreach ($metrics as $metric_name => $value) {
            if (!isset($db_metrics[$metric_name])) {
                // insert new metric
                dbInsert(
                    array(
                        'app_id' => $app['app_id'],
                        'metric' => $metric_name,
                        'value' => $value,
                    ),
                    'application_metrics'
                );
                echo '+';
            } elseif ($value != $db_metrics[$metric_name]['value']) {
                dbUpdate(
                    array(
                        'value' => $value,
                        'value_prev' => $db_metrics[$metric_name]['value'],
                    ),
                    'application_metrics',
                    'app_id=? && metric=?',
                    array($app['app_id'], $metric_name)
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
                array($app['app_id'], $db_metric['metric'])
            );
            echo '-';
        }

        echo PHP_EOL;
    }
}

function convert_to_celsius($value)
{
    $value = ($value - 32) / 1.8;
    return sprintf('%.02f', $value);
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
 * @param integer $min_version the minimum version to accept for the returned JSON. default: 1
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
    $output = snmp_get($device, 'nsExtendOutputFull.'.string_to_oid($extend), '-Oqv', 'NET-SNMP-EXTEND-MIB');

    // make sure we actually get something back
    if (empty($output)) {
        throw new JsonAppPollingFailedException("Empty return from snmp_get.", -2);
    }

    //  turn the JSON into a array
    $parsed_json = json_decode(stripslashes($output), true);

    // improper JSON or something else was returned. Populate the variable with an error.
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new JsonAppParsingFailedException("Invalid JSON", $output, -3);
    }

    // There no keys in the array, meaning '{}' was was returned
    if (empty($parsed_json)) {
        throw new JsonAppBlankJsonException("Blank JSON returned.", $output, -4);
    }

    // It is a legacy JSON app extend, meaning these are not set
    if (!isset($parsed_json['error'], $parsed_json['data'], $parsed_json['errorString'], $parsed_json['version'])) {
        throw new JsonAppMissingKeysException("Legacy script or extend error, missing one or more required keys.", $output, $parsed_json, -5);
    }

    if ($parsed_json['version'] < $min_version) {
        throw new JsonAppWrongVersionException("Script,'".$parsed_json['version']."', older than required version of '$min_version'", $output, $parsed_json, -6);
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
    $return = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (strcmp($prefix, '')) {
                $key=$prefix.$joiner.$key;
            }
            $return = array_merge($return, data_flatten($value, $key, $joiner));
        } else {
            if (strcmp($prefix, '')) {
                $key=$prefix.$joiner.$key;
            }
            $return[$key] = $value;
        }
    }

    return $return;
}
