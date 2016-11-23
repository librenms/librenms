<?php

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
        $multi_response = snmp_get_multi_oid($device, $oids, '-OUQnt');
        $cache = array_merge($cache, $multi_response);
    }
    return $cache;
}

function poll_sensor($device, $class, $unit)
{
    global $config, $memcache, $agent_sensors;

    $sensors = array();
    $misc_sensors = array();
    $all_sensors = array();
    foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ?', array($class, $device['device_id'])) as $sensor) {
        if ($sensor['poller_type'] == 'agent') {
            $misc_sensors[] = $sensor;
        } elseif ($sensor['poller_type'] == 'ipmi') {
            $misc_sensors[] = $sensor;
        } else {
            $sensors[] = $sensor;
        }
    }

    $snmp_data = bulk_sensor_snmpget($device, $sensors);

    foreach ($sensors as $sensor) {
        echo 'Checking (' . $sensor['poller_type'] . ") $class " . $sensor['sensor_descr'] . '... '.PHP_EOL;

        if ($sensor['poller_type'] == 'snmp') {
            $mibdir = null;

            if (file_exists('includes/polling/sensors/'. $class .'/'. $device['os'] .'.inc.php')) {
                require_once 'includes/polling/sensors/'. $class .'/'. $device['os'] .'.inc.php';
            }

            $sensor_value = trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid']]));

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

    foreach ($all_sensors as $sensor) {
        $sensor_value = $sensor['new_value'];
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

        $rrd_name = get_sensor_rrd_name($device, $sensor);
        $rrd_def = 'DS:sensor:GAUGE:600:-20000:20000';

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

        // FIXME also warn when crossing WARN level!!
        if ($sensor['sensor_limit_low'] != '' && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $sensor_value < $sensor['sensor_limit_low'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for '.$device['hostname'].' '.$sensor['sensor_descr']."\n";
            log_event(ucfirst($class).' '.$sensor['sensor_descr'].' under threshold: '.$sensor_value." $unit (< ".$sensor['sensor_limit_low']." $unit)", $device, $class, $sensor['sensor_id']);
        } elseif ($sensor['sensor_limit'] != '' && $sensor['sensor_current'] < $sensor['sensor_limit'] && $sensor_value > $sensor['sensor_limit'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for '.$device['hostname'].' '.$sensor['sensor_descr']."\n";
            log_event(ucfirst($class).' '.$sensor['sensor_descr'].' above threshold: '.$sensor_value." $unit (> ".$sensor['sensor_limit']." $unit)", $device, $class, $sensor['sensor_id']);
        }
        if ($sensor['sensor_class'] == 'state' && $sensor['sensor_current'] != $sensor_value) {
            log_event($class . ' sensor has changed from ' . $sensor['sensor_current'] . ' to ' . $sensor_value, $device, $class, $sensor['sensor_id']);
        }
        dbUpdate(array('sensor_current' => $sensor_value, 'sensor_prev' => $sensor['sensor_current'], 'lastupdate' => array('NOW()')), 'sensors', '`sensor_class` = ? AND `sensor_id` = ?', array($class,$sensor['sensor_id']));
    }
}//end poll_sensor()


function poll_device($device, $options)
{
    global $config, $device, $polled_devices, $memcache;

    $attribs = get_dev_attribs($device['device_id']);
    $device['snmp_max_repeaters'] = $attribs['snmp_max_repeaters'];
    $device['snmp_max_oid'] = $attribs['snmp_max_oid'];

    $status = 0;
    unset($array);
    $device_start = microtime(true);
    // Start counting device poll time
    echo 'Hostname: ' . $device['hostname'] . PHP_EOL;
    echo 'Device ID: ' . $device['device_id'] . PHP_EOL;
    echo 'OS: ' . $device['os'];
    $ip = dnslookup($device);

    if (!empty($ip) && $ip != inet6_ntop($device['ip'])) {
        log_event('Device IP changed to '.$ip, $device, 'system');
        $db_ip = inet_pton($ip);
        dbUpdate(array('ip' => $db_ip), 'devices', 'device_id=?', array($device['device_id']));
    }

    if ($config['os'][$device['os']]['group']) {
        $device['os_group'] = $config['os'][$device['os']]['group'];
        echo ' ('.$device['os_group'].')';
    }

    echo PHP_EOL.PHP_EOL;

    unset($poll_update);
    unset($poll_update_query);
    unset($poll_separator);
    $poll_update_array = array();
    $update_array = array();

    $host_rrd = $config['rrd_dir'].'/'.$device['hostname'];
    if ($config['norrd'] !== true && !is_dir($host_rrd)) {
        mkdir($host_rrd);
        echo "Created directory : $host_rrd\n";
    }

    $address_family = snmpTransportToAddressFamily($device['transport']);

    $ping_response = isPingable($device['hostname'], $address_family, $attribs);

    $device_perf              = $ping_response['db'];
    $device_perf['device_id'] = $device['device_id'];
    $device_perf['timestamp'] = array('NOW()');
    if (can_ping_device($attribs) === true && is_array($device_perf)) {
        dbInsert($device_perf, 'device_perf');
    }

    $device['pingable'] = $ping_response['result'];
    $ping_time          = $ping_response['last_ping_timetaken'];
    $response           = array();
    $status_reason      = '';
    if ($device['pingable']) {
        $device['snmpable'] = isSNMPable($device);
        if ($device['snmpable']) {
            $status                    = '1';
            $response['status_reason'] = '';
        } else {
            echo 'SNMP Unreachable';
            $status                    = '0';
            $response['status_reason'] = 'snmp';
        }
    } else {
        echo 'Unpingable';
        $status                    = '0';
        $response['status_reason'] = 'icmp';
    }

    if ($device['status'] != $status) {
        $poll_update   .= $poll_separator."`status` = '$status'";
        $poll_separator = ', ';

        dbUpdate(array('status' => $status, 'status_reason' => $response['status_reason']), 'devices', 'device_id=?', array($device['device_id']));

        log_event('Device status changed to '.($status == '1' ? 'Up' : 'Down'). ' from ' . $response['status_reason'] . ' check.', $device, ($status == '1' ? 'up' : 'down'));
    }

    if ($status == '1') {
        $graphs    = array();
        $oldgraphs = array();

        // we always want the core module to be included
        include 'includes/polling/core.inc.php';

        $force_module = false;
        if ($options['m']) {
            $config['poller_modules'] = array();
            foreach (explode(',', $options['m']) as $module) {
                if (is_file('includes/polling/'.$module.'.inc.php')) {
                    $config['poller_modules'][$module] = 1;
                    $force_module = true;
                }
            }
        }
        foreach ($config['poller_modules'] as $module => $module_status) {
            $os_module_status = $config['os'][$device['os']]['poller_modules'][$module];
            d_echo("Modules status: Global" . (isset($module_status) ? ($module_status ? '+ ' : '- ') : '  '));
            d_echo("OS" . (isset($os_module_status) ? ($os_module_status ? '+ ' : '- ') : '  '));
            d_echo("Device" . (isset($attribs['poll_' . $module]) ? ($attribs['poll_' . $module] ? '+ ' : '- ') : '  '));
            if ($force_module === true ||
                $attribs['poll_'.$module] ||
                ($os_module_status && !isset($attribs['poll_'.$module])) ||
                ($module_status && !isset($os_module_status) && !isset($attribs['poll_' . $module]))) {
                $module_start = 0;
                $module_time  = 0;
                $module_start = microtime(true);
                echo "\n#### Load poller module $module ####\n";
                include "includes/polling/$module.inc.php";
                $module_time = microtime(true) - $module_start;
                printf("\n>> Runtime for poller module '%s': %.4f seconds\n", $module, $module_time);
                echo "#### Unload poller module $module ####\n\n";

                // save per-module poller stats
                $tags = array(
                    'module'      => $module,
                    'rrd_def'     => 'DS:poller:GAUGE:600:0:U',
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
            } elseif (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] == '0') {
                echo "Module [ $module ] disabled on host.\n\n";
            } elseif (isset($os_module_status) && $os_module_status == '0') {
                echo "Module [ $module ] disabled on os.\n\n";
            } else {
                echo "Module [ $module ] disabled globally.\n\n";
            }
        }

        // Update device_groups
        UpdateGroupsForDevice($device['device_id']);

        if (!isset($options['m'])) {
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
        }//end if

        $device_end  = microtime(true);
        $device_run  = ($device_end - $device_start);
        $device_time = substr($device_run, 0, 5);

        // Poller performance
        if (!empty($device_time)) {
            $tags = array(
                'rrd_def' => 'DS:poller:GAUGE:600:0:U',
                'module'  => 'ALL',
            );
            $fields = array(
                'poller' => $device_time,
            );

            data_update($device, 'poller-perf', $tags, $fields);
        }

        // Ping response
        if (can_ping_device($attribs) === true  &&  !empty($ping_time)) {
            $tags = array(
                'rrd_def' => 'DS:ping:GAUGE:600:0:65535',
            );
            $fields = array(
                'ping' => $ping_time,
            );

            $update_array['last_ping']             = array('NOW()');
            $update_array['last_ping_timetaken']   = $ping_time;

            data_update($device, 'ping-perf', $tags, $fields);
        }

        $update_array['last_polled']           = array('NOW()');
        $update_array['last_polled_timetaken'] = $device_time;

        // echo("$device_end - $device_start; $device_time $device_run");
        echo "Polled in $device_time seconds\n";

        d_echo('Updating '.$device['hostname']."\n");

        $updated = dbUpdate($update_array, 'devices', '`device_id` = ?', array($device['device_id']));
        if ($updated) {
            echo "UPDATED!\n";
        }

        unset($storage_cache);
        // Clear cache of hrStorage ** MAYBE FIXME? **
        unset($cache);
        // Clear cache (unify all things here?)
    }//end if
}//end poll_device()


function poll_mib_def($device, $mib_name_table, $mib_subdir, $mib_oids, $mib_graphs, &$graphs)
{
    echo "This is poll_mib_def Processing\n";
    $mib = null;

    if (stristr($mib_name_table, 'UBNT')) {
        list($mib,) = explode(':', $mib_name_table, 2);
        $measurement_name = strtolower($mib);
    } else {
        list($mib,$file) = explode(':', $mib_name_table, 2);
        $measurement_name = strtolower($file);
    }

    $rrd_def = array();
    $oidglist  = array();
    $oidnamelist = array();
    foreach ($mib_oids as $oid => $param) {
        $oidindex  = $param[0];
        $oiddsname = $param[1];
        $oiddsdesc = $param[2];
        $oiddstype = $param[3];
        $oiddsopts = $param[4];

        if (strlen($oiddsname) > 19) {
            $oiddsname = substr($oiddsname, 0, 19);
        }

        if (empty($oiddsopts)) {
            $oiddsopts = '600:U:100000000000';
        }

        $rrd_def[] = 'DS:'.$oiddsname.':'.$oiddstype.':'.$oiddsopts;

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
    data_update($device, $measurement_name, $tags, $fields);

    foreach ($mib_graphs as $graphtoenable) {
        $graphs[$graphtoenable] = true;
    }

    return true;
}//end poll_mib_def()


function get_main_serial($device)
{
    if ($device['os_group'] == 'cisco') {
        $serial_output = snmp_get_multi($device, 'entPhysicalSerialNum.1 entPhysicalSerialNum.1001', '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');
        if (!empty($serial_output[1]['entPhysicalSerialNum'])) {
            return $serial_output[1]['entPhysicalSerialNum'];
        } elseif (!empty($serial_output[1001]['entPhysicalSerialNum'])) {
            return $serial_output[1001]['entPhysicalSerialNum'];
        }
    }
}//end get_main_serial()


function location_to_latlng($device)
{
    global $config;
    if (function_check('curl_version') !== true) {
        d_echo("Curl support for PHP not enabled\n");
        return false;
    }
    $bad_loc = false;
    $device_location = $device['location'];
    if (!empty($device_location)) {
        $new_device_location = preg_replace("/ /", "+", $device_location);
        // We have a location string for the device.
        $loc = parse_location($device_location);
        if (!is_array($loc)) {
            $loc = dbFetchRow("SELECT `lat`,`lng` FROM `locations` WHERE `location`=? LIMIT 1", array($device_location));
        }
        if (is_array($loc) === false) {
            // Grab data from which ever Geocode service we use.
            switch ($config['geoloc']['engine']) {
                case "google":
                default:
                    d_echo("Google geocode engine being used\n");
                    $api_key = ($config['geoloc']['api_key']);
                    if (!empty($api_key)) {
                        d_echo("Use Google API key: $api_key\n");
                        $api_url = "https://maps.googleapis.com/maps/api/geocode/json?address=$new_device_location&key=$api_key";
                    } else {
                        $api_url = "https://maps.googleapis.com/maps/api/geocode/json?address=$new_device_location";
                    }
                    break;
            }
            $curl_init = curl_init($api_url);
            set_curl_proxy($curl_init);
            curl_setopt($curl_init, CURLOPT_RETURNTRANSFER, true);
            $data = json_decode(curl_exec($curl_init), true);
            // Parse the data from the specific Geocode services.
            switch ($config['geoloc']['engine']) {
                case "google":
                default:
                    if ($data['status'] == 'OK') {
                        $loc = $data['results'][0]['geometry']['location'];
                    } else {
                        $bad_loc = true;
                    }
                    break;
            }
            if ($bad_loc === true) {
                d_echo("Bad lat / lng received\n");
            } else {
                $loc['timestamp'] = array('NOW()');
                $loc['location'] = $device_location;
                if (dbInsert($loc, 'locations')) {
                    d_echo("Device lat/lng created\n");
                } else {
                    d_echo("Device lat/lng could not be created\n");
                }
            }
        } else {
            d_echo("Using cached lat/lng from other device\n");
        }
    }
}// end location_to_latlng()

/**
 * @param $device
 * @return int|null
 */
function get_device_oid_limit($device)
{
    global $config;

    $max_oid = $device['snmp_max_oid'];

    if (isset($max_oid) && $max_oid > 0) {
        return $max_oid;
    } elseif (isset($config['snmp']['max_oid']) && $config['snmp']['max_oid'] > 0) {
        return $config['snmp']['max_oid'];
    } else {
        return 10;
    }
}
