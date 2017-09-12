<?php
echo ' ENTITY-SENSOR: ';
echo 'Caching OIDs:';
if (!is_array($entity_array)) {
    $entity_array = array();
    echo ' entPhysicalDescr';
    $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalDescr', $entity_array, 'CISCO-ENTITY-SENSOR-MIB');
    echo ' entPhysicalName';
    $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $entity_array, 'CISCO-ENTITY-SENSOR-MIB');
}
$oids = array();
echo ' entPhySensorType';
$oids = snmpwalk_cache_multi_oid($device, 'entPhySensorType', $oids, 'ENTITY-SENSOR-MIB');
echo ' entPhySensorScale';
$oids = snmpwalk_cache_multi_oid($device, 'entPhySensorScale', $oids, 'ENTITY-SENSOR-MIB');
echo ' entPhySensorPrecision';
$oids = snmpwalk_cache_multi_oid($device, 'entPhySensorPrecision', $oids, 'ENTITY-SENSOR-MIB');
echo ' entPhySensorValue';
$oids = snmpwalk_cache_multi_oid($device, 'entPhySensorValue', $oids, 'ENTITY-SENSOR-MIB');
if ($device['os'] === 'arista_eos') {
    require 'includes/discovery/sensors/misc/arista-eos-limits.inc.php';
}
$entitysensor['voltsDC']   = 'voltage';
$entitysensor['voltsAC']   = 'voltage';
$entitysensor['amperes']   = 'current';
$entitysensor['watts']     = 'power';
$entitysensor['hertz']     = 'freq';
$entitysensor['percentRH'] = 'humidity';
$entitysensor['rpm']       = 'fanspeed';
$entitysensor['celsius']   = 'temperature';
$entitysensor['dBm']       = 'dbm';
if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $low_limit      = null;
        $low_warn_limit = null;
        $warn_limit     = null;
        $high_limit     = null;

        // Fix for Cisco ASR920, 15.5(2)S
        if ($entry['entPhySensorType'] == 'other' && str_contains($entity_array[$index]['entPhysicalName'], array('Rx Power Sensor', 'Tx Power Sensor'))) {
            $entitysensor['other'] = 'dbm';
        }
        if ($entitysensor[$entry['entPhySensorType']] && is_numeric($entry['entPhySensorValue']) && is_numeric($index)) {
            $entPhysicalIndex = $index;
            $oid              = '.1.3.6.1.2.1.99.1.1.1.4.'.$index;
            $current          = $entry['entPhySensorValue'];
            // ENTITY-SENSOR-MIB::entPhySensorUnitsDisplay.11 = STRING: "C"
            $descr = ucwords($entity_array[$index]['entPhysicalName']);
            // if ($descr || $device['os'] == "iosxr")
            if ($descr) {
                $descr = rewrite_entity_descr($descr);
            } else {
                $descr = $entity_array[$index]['entPhysicalDescr'];
                $descr = rewrite_entity_descr($descr);
            }
            $valid_sensor = check_entity_sensor($descr, $device);
            $type = $entitysensor[$entry['entPhySensorType']];
            // FIXME this stuff is foul
            if ($entry['entPhySensorScale'] == 'nano') {
                $divisor    = '1000000000';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'micro') {
                $divisor    = '1000000';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'milli') {
                $divisor    = '1000';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'units') {
                $divisor    = '1';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'kilo') {
                $divisor    = '1';
                $multiplier = '1000';
            }
            if ($entry['entPhySensorScale'] == 'mega') {
                $divisor    = '1';
                $multiplier = '1000000';
            }
            if ($entry['entPhySensorScale'] == 'giga') {
                $divisor    = '1';
                $multiplier = '1000000000';
            }
            if ($entry['entPhySensorScale'] == 'yocto') {
                $divisor    = '1';
                $multiplier = '1';
            }
            if (is_numeric($entry['entPhySensorPrecision']) && $entry['entPhySensorPrecision'] > '0') {
                $divisor = $divisor.str_pad('', $entry['entPhySensorPrecision'], '0');
            }

            if ($device['os'] === 'arista_eos') {
                if ($entry['entPhySensorScale'] == 'milli' && $entry['entPhySensorType'] == 'amperes') {
                    $divisor = '1';
                    $multiplier = '1';
                }
            }

            $current = ($current * $multiplier / $divisor);
            if ($type == 'temperature') {
                if ($current > '200') {
                    $valid_sensor = false;
                }
                $descr = preg_replace('/[T|t]emperature[|s]/', '', $descr);
            }
            if ($device['os'] == 'rittal-lcp') {
                if ($type == 'voltage') {
                    $divisor = 1000;
                }
                if ($descr == 'Temperature.Value') {
                    $divisor = 1000;
                }
                if ($descr == 'System.Temperature.Value') {
                    $divisor = 1000;
                }
                if ($type == 'humidity' && $current == '0') {
                    $valid_sensor = false;
                }
            }
            // echo($descr . "|" . $index . "|" .$current . "|" . $multiplier . "|" . $divisor ."|" . $entry['entPhySensorScale'] . "|" . $entry['entPhySensorPrecision'] . "\n");
            if ($current == '-127' || ($device['os'] == 'asa' && str_contains($device['hardware'], 'sc'))) {
                $valid_sensor = false;
            }
            if ($valid_sensor && dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE device_id = ? AND `sensor_class` = ? AND `sensor_type` = 'cisco-entity-sensor' AND `sensor_index` = ?", array($device['device_id'], $type, $index)) == '0') {
                // Check to make sure we've not already seen this sensor via cisco's entity sensor mib
                if ($type == "power" && $device['os'] == "arista_eos" && preg_match("/DOM (R|T)x Power/i", $descr)) {
                    $type = "dbm";
                    $current = round(10 * log10($entry['entPhySensorValue'] / 10000), 3);
                    $multiplier = 1;
                    $divisor = 1;
                }

                if ($device['os'] === 'arista_eos') {
                    if ($entry['aristaEntSensorThresholdLowWarning'] != '-1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' &&  $entry['entPhySensorType'] == 'watts') {
                            $temp_low_warn_limit = $entry['aristaEntSensorThresholdLowWarning'] / 10000;
                            $low_warn_limit = round(10 * log10($temp_low_warn_limit), 2);
                        } else {
                            $low_warn_limit = $entry['aristaEntSensorThresholdLowWarning'] / $divisor;
                        }
                    }
                    if ($entry['aristaEntSensorThresholdLowCritical'] != '-1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' &&  $entry['entPhySensorType'] == 'watts') {
                            $temp_low_limit = $entry['aristaEntSensorThresholdLowCritical'] / 10000;
                            $low_limit = round(10 * log10($temp_low_limit), 2);
                        } else {
                            $low_limit = $entry['aristaEntSensorThresholdLowCritical'] / $divisor;
                        }
                    }
                    if ($entry['aristaEntSensorThresholdHighWarning'] != '1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' &&  $entry['entPhySensorType'] == 'watts') {
                            $temp_warn_limit = $entry['aristaEntSensorThresholdHighWarning'] / 10000;
                            $warn_limit = round(10 * log10($temp_warn_limit), 2);
                        } else {
                            $warn_limit = $entry['aristaEntSensorThresholdHighWarning'] / $divisor;
                        }
                    }
                    if ($entry['aristaEntSensorThresholdHighCritical'] != '1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' &&  $entry['entPhySensorType'] == 'watts') {
                            $temp_high_limit = $entry['aristaEntSensorThresholdHighCritical'] / 10000;
                            $high_limit = round(10 * log10($temp_high_limit), 2);
                        } else {
                            $high_limit = $entry['aristaEntSensorThresholdHighCritical'] / $divisor;
                        }
                    }
                }
                discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'entity-sensor', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex, $entry['entSensorMeasuredEntity']);
            }
        }//end if
    }//end foreach
    unset(
        $entity_array
    );
}//end if
echo "\n";
