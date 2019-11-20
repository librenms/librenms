<?php

if ($device['os_group'] == 'cisco') {
    echo ' CISCO-ENTITY-SENSOR: ';

    $oids = [];
    echo 'Caching OIDs:';

    if (empty($entity_array)) {
        $tmp_oids = ['entPhysicalDescr', 'entPhysicalName', 'entPhysicalClass', 'entPhysicalContainedIn', 'entPhysicalParentRelPos'];
        $entity_array = [];
        foreach ($tmp_oids as $tmp_oid) {
            echo " $tmp_oid";
            $entity_array = snmpwalk_cache_multi_oid($device, $tmp_oid, $entity_array, 'ENTITY-MIB:CISCO-ENTITY-SENSOR-MIB');
        }
        echo ' entAliasMappingIdentifier';
        $entity_array = snmpwalk_cache_twopart_oid($device, 'entAliasMappingIdentifier', $entity_array, 'ENTITY-MIB:IF-MIB');
    }

    echo '  entSensorType';
    $oids = snmpwalk_cache_multi_oid($device, 'entSensorType', $oids, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entSensorScale';
    $oids = snmpwalk_cache_multi_oid($device, 'entSensorScale', $oids, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entSensorValue';
    $oids = snmpwalk_cache_multi_oid($device, 'entSensorValue', $oids, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entSensorMeasuredEntity';
    $oids = snmpwalk_cache_multi_oid($device, 'entSensorMeasuredEntity', $oids, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entSensorPrecision';
    $oids = snmpwalk_cache_multi_oid($device, 'entSensorPrecision', $oids, 'CISCO-ENTITY-SENSOR-MIB');

    $t_oids = [];
    echo ' entSensorThresholdSeverity';
    $t_oids = snmpwalk_cache_twopart_oid($device, 'entSensorThresholdSeverity', $t_oids, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entSensorThresholdRelation';
    $t_oids = snmpwalk_cache_twopart_oid($device, 'entSensorThresholdRelation', $t_oids, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entSensorThresholdValue';
    $t_oids = snmpwalk_cache_twopart_oid($device, 'entSensorThresholdValue', $t_oids, 'CISCO-ENTITY-SENSOR-MIB');

    d_echo($oids);

    $entitysensor['voltsDC']   = 'voltage';
    $entitysensor['voltsAC']   = 'voltage';
    $entitysensor['amperes']   = 'current';
    $entitysensor['watt']      = 'power';
    $entitysensor['hertz']     = 'freq';
    $entitysensor['percentRH'] = 'humidity';
    $entitysensor['rpm']       = 'fanspeed';
    $entitysensor['celsius']   = 'temperature';
    $entitysensor['watts']     = 'power';
    $entitysensor['dBm']       = 'dbm';

    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            // echo("[" . $entry['entSensorType'] . "|" . $entry['entSensorValue']. "|" . $index . "]");
            if ($entitysensor[$entry['entSensorType']] && is_numeric($entry['entSensorValue']) && is_numeric($index)) {
                $entPhysicalIndex = $index;
                if ($entity_array[$index]['entPhysicalName'] || $device['os'] == 'iosxr') {
                    $descr = rewrite_entity_descr($entity_array[$index]['entPhysicalName']);
                } else {
                    $descr = rewrite_entity_descr($entity_array[$index]['entPhysicalDescr']);
                }

                // Set description based on measured entity if it exists
                if (is_numeric($entry['entSensorMeasuredEntity']) && $entry['entSensorMeasuredEntity']) {
                    $measured_descr = $entity_array[$entry['entSensorMeasuredEntity']]['entPhysicalName'];
                    if (!$measured_descr) {
                        $measured_descr = $entity_array[$entry['entSensorMeasuredEntity']]['entPhysicalDescr'];
                    }

                    $descr = $measured_descr.' - '.$descr;
                }

                // Bit dirty also, clean later
                $descr = str_replace('Temp: ', '', $descr);
                $descr = str_ireplace('temperature ', '', $descr);

                $oid     = '.1.3.6.1.4.1.9.9.91.1.1.1.1.4.'.$index;
                $current = $entry['entSensorValue'];
                $type    = $entitysensor[$entry['entSensorType']];

                // echo("$index : ".$entry['entSensorScale']."|");
                // FIXME this stuff is foul
                if ($entry['entSensorScale'] == 'nano') {
                    $divisor    = '1000000000';
                    $multiplier = '1';
                }

                if ($entry['entSensorScale'] == 'micro') {
                    $divisor    = '1000000';
                    $multiplier = '1';
                }

                if ($entry['entSensorScale'] == 'milli') {
                    $divisor    = '1000';
                    $multiplier = '1';
                }

                if ($entry['entSensorScale'] == 'units') {
                    $divisor    = '1';
                    $multiplier = '1';
                }

                if ($entry['entSensorScale'] == 'kilo') {
                    $divisor    = '1';
                    $multiplier = '1000';
                }

                if ($entry['entSensorScale'] == 'mega') {
                    $divisor    = '1';
                    $multiplier = '1000000';
                }

                if ($entry['entSensorScale'] == 'giga') {
                    $divisor    = '1';
                    $multiplier = '1000000000';
                }

                if (is_numeric($entry['entSensorPrecision']) && $entry['entSensorPrecision'] > '0') {
                    $divisor = $divisor.str_pad('', $entry['entSensorPrecision'], '0');
                }

                $current = ($current * $multiplier / $divisor);

                // Set thresholds to null
                $limit          = null;
                $limit_low      = null;
                $warn_limit     = null;
                $warn_limit_low = null;

                // Check thresholds for this entry (bit dirty, but it works!)
                if (is_array($t_oids[$index])) {
                    foreach ($t_oids[$index] as $t_index => $key) {
                        // Critical Limit
                        if ($key['entSensorThresholdSeverity'] == 'major' && $key['entSensorThresholdRelation'] == 'greaterOrEqual') {
                            $limit = ($key['entSensorThresholdValue'] * $multiplier / $divisor);
                        }

                        if ($key['entSensorThresholdSeverity'] == 'major' && $key['entSensorThresholdRelation'] == 'lessOrEqual') {
                            $limit_low = ($key['entSensorThresholdValue'] * $multiplier / $divisor);
                        }

                        // Warning Limit
                        if ($key['entSensorThresholdSeverity'] == 'minor' && $key['entSensorThresholdRelation'] == 'greaterOrEqual') {
                            $warn_limit = ($key['entSensorThresholdValue'] * $multiplier / $divisor);
                        }

                        if ($key['entSensorThresholdSeverity'] == 'minor' && $key['entSensorThresholdRelation'] == 'lessOrEqual') {
                            $warn_limit_low = ($key['entSensorThresholdValue'] * $multiplier / $divisor);
                        }
                    }//end foreach
                }//end if

                // End Threshold code
                $ok = true;

                if ($current == '-127' || $descr == '') {
                    $ok = false;
                }

                if ($ok) {
                    $phys_index = $entity_array[$index]['entPhysicalContainedIn'];
                    while ($phys_index != 0) {
                        if ($index === $phys_index) {
                            break;
                        }
                        if ($entity_array[$phys_index]['entPhysicalClass'] === 'port') {
                            if (str_contains($entity_array[$phys_index][0]['entAliasMappingIdentifier'], 'ifIndex.')) {
                                list(, $tmp_ifindex) = explode(".", $entity_array[$phys_index][0]['entAliasMappingIdentifier']);
                                $tmp_port = get_port_by_index_cache($device['device_id'], $tmp_ifindex);
                                if (is_array($tmp_port)) {
                                    $entPhysicalIndex                 = $tmp_ifindex;
                                    $entry['entSensorMeasuredEntity'] = 'ports';
                                }
                            }
                            break;
                        } else {
                            $phys_index = $entity_array[$phys_index]['entPhysicalContainedIn'];
                        }
                    }
                    discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'cisco-entity-sensor', ucwords($descr), $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entry['entSensorMeasuredEntity'], null);
                    #Cisco IOS-XR : add a fake sensor to graph as dbm
                    if ($type == "power" and $device['os'] == "iosxr" and (preg_match("/power (R|T)x/i", $descr) or preg_match("/(R|T)x Power/i", $descr))) {
                            // convert Watts to dbm
                            $type = "dbm";
                            $limit_low = 10 * log10($limit_low*1000);
                            $warn_limit_low = 10 * log10($warn_limit_low*1000);
                            $warn_limit = 10 * log10($warn_limit*1000);
                            $limit = 10 * log10($limit*1000);
                            $current = round(10 * log10($current*1000), 3);
                            $multiplier = 1;
                            $divisor = 1;
                            //echo("\n".$valid['sensor'].", $type, $device, $oid, $index, 'cisco-entity-sensor', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current");
                            discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'cisco-entity-sensor', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entry['entSensorMeasuredEntity'], null);
                    }
                }

                $cisco_entity_temperature = 1;
                unset($limit, $limit_low, $warn_limit, $warn_limit_low);
            }//end if
        }//end foreach
    }//end if
    unset(
        $entity_array
    );
}//end if
