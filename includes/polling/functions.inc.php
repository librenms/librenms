<?php


function poll_sensor($device, $class, $unit) {
    global $config, $memcache, $agent_sensors;

    foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ?', array($class, $device['device_id'])) as $sensor) {
        echo 'Checking ('.$sensor['poller_type'].") $class ".$sensor['sensor_descr'].'... ';
        $sensor_value = '';

        if ($sensor['poller_type'] == 'snmp') {
            if ($device['os'] == 'siklu') {
                $mib = ':RADIO-BRIDGE-MIB';
            }
            else {
                $mib = '';
            }

            if ($class == 'temperature') {
                if ($device['os'] == 'netapp') {
                    include 'includes/polling/temperatures/netapp.inc.php';
                }
                else {
                    // Try 5 times to get a valid temp reading
                    for ($i = 0; $i < 5; $i++) {
                        d_echo("Attempt $i ");

                        $sensor_value = trim(str_replace('"', '', snmp_get($device, $sensor['sensor_oid'], '-OUqnv', "SNMPv2-MIB$mib")));
                        preg_match('/[\d\.]+/', $sensor_value, $temp_response);
                        if (!empty($temp_response[0])) {
                            $sensor_value = $temp_response[0];
                        }

                        if (is_numeric($sensor_value) && $sensor_value != 9999) {
                            break;
                            // TME sometimes sends 999.9 when it is right in the middle of an update;
                        }              sleep(1);
                        // end if
                    }
                }//end if
            }
            else if ($class == 'state') {
                $sensor_value = trim(str_replace('"', '', snmp_walk($device, $sensor['sensor_oid'], '-Oevq', 'SNMPv2-MIB')));
            }
            else if ($class == 'dbm') {
                $sensor_value = trim(str_replace('"', '', snmp_get($device, $sensor['sensor_oid'], '-OUqnv', "SNMPv2-MIB$mib")));
                //iosxr does not expose dbm values through SNMP so we convert Watts to dbm to have a nice graph to show
                if ($device['os'] == "iosxr") {
                    $sensor_value = round(10*log10($sensor_value/1000),3);
                }
            }
            else {
                if ($sensor['sensor_type'] == 'apc') {
                    $sensor_value = trim(str_replace('"', '', snmp_walk($device, $sensor['sensor_oid'], '-OUqnv', "SNMPv2-MIB:PowerNet-MIB$mib")));
                }
                else {
                    $sensor_value = trim(str_replace('"', '', snmp_get($device, $sensor['sensor_oid'], '-OUqnv', "SNMPv2-MIB$mib")));
                }
            }//end if
            unset($mib);
        }
        else if ($sensor['poller_type'] == 'agent') {
            if (isset($agent_sensors)) {
                $sensor_value = $agent_sensors[$class][$sensor['sensor_type']][$sensor['sensor_index']]['current'];
            }
            else {
                echo "no agent data!\n";
                continue;
            }
        }
        else if ($sensor['poller_type'] == 'ipmi') {
            echo " already polled.\n";
            // ipmi should probably move here from the ipmi poller file (FIXME)
            continue;
        }
        else {
            echo "unknown poller type!\n";
            continue;
        }//end if

        if ($sensor_value == -32768) {
            echo 'Invalid (-32768) ';
            $sensor_value = 0;
        }

        if ($sensor['sensor_divisor']) {
            $sensor_value = ($sensor_value / $sensor['sensor_divisor']);
        }

        if ($sensor['sensor_multiplier']) {
            $sensor_value = ($sensor_value * $sensor['sensor_multiplier']);
        }

        $rrd_file = get_sensor_rrd($device, $sensor);

        if (!is_file($rrd_file)) {
            rrdtool_create(
                $rrd_file,
                '--step 300 
                DS:sensor:GAUGE:600:-20000:20000 '.$config['rrd_rra']
            );
        }

        echo "$sensor_value $unit\n";

        $fields = array(
            'sensor' => $sensor_value,
        );

        rrdtool_update($rrd_file, $fields);

        // FIXME also warn when crossing WARN level!!
        if ($sensor['sensor_limit_low'] != '' && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $sensor_value <= $sensor['sensor_limit_low'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for '.$device['hostname'].' '.$sensor['sensor_descr']."\n";
            log_event(ucfirst($class).' '.$sensor['sensor_descr'].' under threshold: '.$sensor_value." $unit (< ".$sensor['sensor_limit_low']." $unit)", $device, $class, $sensor['sensor_id']);
        }
        else if ($sensor['sensor_limit'] != '' && $sensor['sensor_current'] < $sensor['sensor_limit'] && $sensor_value >= $sensor['sensor_limit'] && $sensor['sensor_alert'] == 1) {
            echo 'Alerting for '.$device['hostname'].' '.$sensor['sensor_descr']."\n";
            log_event(ucfirst($class).' '.$sensor['sensor_descr'].' above threshold: '.$sensor_value." $unit (> ".$sensor['sensor_limit']." $unit)", $device, $class, $sensor['sensor_id']);
        }

        dbUpdate(array('sensor_current' => $sensor_value), 'sensors', '`sensor_class` = ? AND `sensor_id` = ?', array($class, $sensor['sensor_id']));
    }//end foreach

}//end poll_sensor()


function poll_device($device, $options) {
    global $config, $device, $polled_devices, $db_stats, $memcache;

    $attribs = get_dev_attribs($device['device_id']);

    $status = 0;
    unset($array);
    $device_start = utime();
    // Start counting device poll time
    echo $device['hostname'].' '.$device['device_id'].' '.$device['os'].' ';
    if ($config['os'][$device['os']]['group']) {
        $device['os_group'] = $config['os'][$device['os']]['group'];
        echo '('.$device['os_group'].')';
    }

    echo "\n";

    unset($poll_update);
    unset($poll_update_query);
    unset($poll_separator);
    $poll_update_array = array();
    $update_array = array();

    $host_rrd = $config['rrd_dir'].'/'.$device['hostname'];
    if (!is_dir($host_rrd)) {
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
        }
        else {
            echo 'SNMP Unreachable';
            $status                    = '0';
            $response['status_reason'] = 'snmp';
        }
    }
    else {
        echo 'Unpingable';
        $status                    = '0';
        $response['status_reason'] = 'icmp';
    }

    if ($device['status'] != $status) {
        $poll_update   .= $poll_separator."`status` = '$status'";
        $poll_separator = ', ';

        dbUpdate(array('status' => $status, 'status_reason' => $response['status_reason']), 'devices', 'device_id=?', array($device['device_id']));
        dbInsert(array('importance' => '0', 'device_id' => $device['device_id'], 'message' => 'Device is '.($status == '1' ? 'up' : 'down')), 'alerts');

        log_event('Device status changed to '.($status == '1' ? 'Up' : 'Down'), $device, ($status == '1' ? 'up' : 'down'));
    }

    if ($status == '1') {
        $graphs    = array();
        $oldgraphs = array();

        if ($options['m']) {
            foreach (explode(',', $options['m']) as $module) {
                if (is_file('includes/polling/'.$module.'.inc.php')) {
                    include 'includes/polling/'.$module.'.inc.php';
                }
            }
        }
        else {
            foreach ($config['poller_modules'] as $module => $module_status) {
                if ($attribs['poll_'.$module] || ( $module_status && !isset($attribs['poll_'.$module]))) {
                    // TODO per-module polling stats
                    include 'includes/polling/'.$module.'.inc.php';
                }
                else if (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] == '0') {
                    echo "Module [ $module ] disabled on host.\n";
                }
                else {
                    echo "Module [ $module ] disabled globally.\n";
                }
            }
        }//end if

        if (!$options['m']) {
            // FIXME EVENTLOGGING -- MAKE IT SO WE DO THIS PER-MODULE?
            // This code cycles through the graphs already known in the database and the ones we've defined as being polled here
            // If there any don't match, they're added/deleted from the database.
            // Ideally we should hold graphs for xx days/weeks/polls so that we don't needlessly hide information.
            foreach (dbFetch('SELECT `graph` FROM `device_graphs` WHERE `device_id` = ?', array($device['device_id'])) as $graph) {
                if (isset($graphs[$graph['graph']])) {
                    $oldgraphs[$graph['graph']] = true;
                }
                else {
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

        $device_end  = utime();
        $device_run  = ($device_end - $device_start);
        $device_time = substr($device_run, 0, 5);

        // TODO: These should be easy converts to rrd_create_update()
        // Poller performance rrd
        $poller_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/poller-perf.rrd';
        if (!is_file($poller_rrd)) {
            rrdtool_create($poller_rrd, 'DS:poller:GAUGE:600:0:U '.$config['rrd_rra']);
        }

        if (!empty($device_time)) {
            $fields = array(
                'poller' => $device_time,
            );
            rrdtool_update($poller_rrd, $fields);
        }

        // Ping response rrd
        if (can_ping_device($attribs) === true) {
            $ping_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/ping-perf.rrd';
            if (!is_file($ping_rrd)) {
                rrdtool_create($ping_rrd, 'DS:ping:GAUGE:600:0:65535 '.$config['rrd_rra']);
            }

            if (!empty($ping_time)) {
                $fields = array(
                    'ping' => $ping_time,
                );

                rrdtool_update($ping_rrd, $fields);
            }

            $update_array['last_ping']             = array('NOW()');
            $update_array['last_ping_timetaken']   = $ping_time;

        }

        $update_array['last_polled']           = array('NOW()');
        $update_array['last_polled_timetaken'] = $device_time;

        // echo("$device_end - $device_start; $device_time $device_run");
        echo "Polled in $device_time seconds\n";

        d_echo('Updating '.$device['hostname']."\n");
        d_echo($update_array);

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


function poll_mib_def($device, $mib_name_table, $mib_subdir, $mib_oids, $mib_graphs, &$graphs) {
    global $config;

    echo "This is poll_mib_def Processing\n";
    $mib = null;

    if (stristr($mib_name_table, 'UBNT')) {
        list($mib,) = explode(':', $mib_name_table, 2);
        // $mib_dirs = mib_dirs($mib_subdir);
        $rrd_file = strtolower(safename($mib)).'.rrd';
    }
    else {
        list($mib,$file) = explode(':', $mib_name_table, 2);
        $rrd_file        = strtolower(safename($file)).'.rrd';
    }

    $rrdcreate = '--step 300 ';
    $oidglist  = array();
    $oidnamelist = array();
    foreach ($mib_oids as $oid => $param) {
        $oidindex  = $param[0];
        $oiddsname = $param[1];
        $oiddsdesc = $param[2];
        $oiddstype = $param[3];
        $oiddsopts = $param[4];

        if (strlen($oiddsname) > 19) {
            $oiddsname = truncate($oiddsname, 19, '');
        }

        if (empty($oiddsopts)) {
            $oiddsopts = '600:U:100000000000';
        }

        $rrdcreate .= ' DS:'.$oiddsname.':'.$oiddstype.':'.$oiddsopts;

        if ($oidindex != '') {
            $fulloid = $oid.'.'.$oidindex;
        }
        else {
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
        if (is_numeric($snmpdata[$splitindex][$splitoid])) {
            $fields[$oidnamelist[$oid_count]] = $snmpdata[$splitindex][$splitoid];
        }
        else {
            $fields[$oidnamelist[$oid_count]] = 'U';
        }
        $oid_count++;
    }

    $rrdfilename = $config['rrd_dir'].'/'.$device['hostname'].'/'.$rrd_file;

    if (!is_file($rrdfilename)) {
        rrdtool_create($rrdfilename, $rrdcreate.' '.$config['rrd_rra']);
    }

    rrdtool_update($rrdfilename, $fields);

    foreach ($mib_graphs as $graphtoenable) {
        $graphs[$graphtoenable] = true;
    }

    return true;

}//end poll_mib_def()


/*
 * Please use this instead of creating & updating RRD files manually.
 * @param device Device object - only 'hostname' is used at present
 * @param name Array of rrdname components
 * @param def Array of data definitions
 * @param val Array of value definitions
 *
 */


function rrd_create_update($device, $name, $def, $val, $step=300) {
    global $config;
    $rrd = rrd_name($device['hostname'], $name);

    if (!is_file($rrd) && $def != null) {
        // add the --step and the rra definitions to the array
        $newdef = "--step $step ".implode(' ', $def).$config['rrd_rra'];
        rrdtool_create($rrd, $newdef);
    }

    rrdtool_update($rrd, $val);

}//end rrd_create_update()


function get_main_serial($device) {
    if ($device['os_group'] == 'cisco') {
        $serial_output = snmp_get_multi($device, 'entPhysicalSerialNum.1 entPhysicalSerialNum.1001', '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');
        if (!empty($serial_output[1]['entPhysicalSerialNum'])) {
            return $serial_output[1]['entPhysicalSerialNum'];
        }
        else if (!empty($serial_output[1001]['entPhysicalSerialNum'])) {
            return $serial_output[1001]['entPhysicalSerialNum'];
        }
    }

}//end get_main_serial()


function location_to_latlng($device) {
    global $config;
    if (function_check('curl_version') !== true) {
        d_echo("Curl support for PHP not enabled\n");
        return false;
    }
    $bad_loc = false;
    $device_location = $device['location'];
    if (!empty($device_location)) {
        $new_device_location = preg_replace("/ /","+",$device_location);
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
            $data = json_decode(curl_exec($curl_init),true);
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
            }
            else {
                $loc['timestamp'] = array('NOW()');
                $loc['location'] = $device_location;
                if (dbInsert($loc, 'locations')) {
                    d_echo("Device lat/lng created\n");
                }
                else {
                    d_echo("Device lat/lng could not be created\n");
                }
            }
        }
        else {
            d_echo("Using cached lat/lng from other device\n");
        }
    }
}// end location_to_latlng()
