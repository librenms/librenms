<?php

use Illuminate\Support\Str;

echo ' ENTITY-SENSOR: ';
echo 'Caching OIDs:';
if (empty($entity_array)) {
    $entity_array = [];
    echo ' entPhysicalDescr';
    $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalDescr', $entity_array, 'ENTITY-MIB');
    if (! empty($entity_array)) {
        echo ' entPhysicalName';
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $entity_array, 'ENTITY-MIB');
    }
}

if (! empty($entity_array)) {
    echo ' entPhySensorType';
    $entity_oids = snmpwalk_cache_multi_oid($device, 'entPhySensorType', [], 'ENTITY-SENSOR-MIB');
    echo ' entPhySensorScale';
    $entity_oids = snmpwalk_cache_multi_oid($device, 'entPhySensorScale', $entity_oids, 'ENTITY-SENSOR-MIB');
    echo ' entPhySensorPrecision';
    $entity_oids = snmpwalk_cache_multi_oid($device, 'entPhySensorPrecision', $entity_oids, 'ENTITY-SENSOR-MIB');
    echo ' entPhySensorValue';
    $entity_oids = snmpwalk_cache_multi_oid($device, 'entPhySensorValue', $entity_oids, 'ENTITY-SENSOR-MIB');
    if ($device['os'] === 'arista_eos') {
        $entity_oids = snmpwalk_cache_oid($device, 'aristaEntSensorThresholdTable', $entity_oids, 'ARISTA-ENTITY-SENSOR-MIB');
    }
    echo ' entPhySensorOperStatus';
    $entity_oids = snmpwalk_cache_multi_oid($device, 'entPhySensorOperStatus', $entity_oids, 'ENTITY-SENSOR-MIB');
}

if (! empty($entity_oids)) {
    $entitysensor = [
        'voltsDC'   => 'voltage',
        'voltsAC'   => 'voltage',
        'amperes'   => 'current',
        'watts'     => 'power',
        'hertz'     => 'freq',
        'percentRH' => 'humidity',
        'rpm'       => 'fanspeed',
        'celsius'   => 'temperature',
        'dBm'       => 'dbm',
    ];

    foreach ($entity_oids as $index => $entry) {
        $low_limit = null;
        $low_warn_limit = null;
        $warn_limit = null;
        $high_limit = null;

        // Fix for Cisco ASR920, 15.5(2)S
        if ($entry['entPhySensorType'] == 'other' && Str::contains($entity_array[$index]['entPhysicalName'], ['Rx Power Sensor', 'Tx Power Sensor'])) {
            $entitysensor['other'] = 'dbm';
        }
        if ($entitysensor[$entry['entPhySensorType']] && is_numeric($entry['entPhySensorValue']) && is_numeric($index)) {
            $entPhysicalIndex = $index;
            $oid = '.1.3.6.1.2.1.99.1.1.1.4.' . $index;
            $current = $entry['entPhySensorValue'];
            if ($device['os'] === 'arris-d5') {
                $card = str_split($index);
                if (count($card) === 3) {
                    $card = $card[0] . '00';
                } elseif (count($card) === 4) {
                    $card = $card[0] . $card[1] . '00';
                }
                $descr = ucwords($entity_array[$card]['entPhysicalName']) . ' ' . ucwords($entity_array[$index]['entPhysicalDescr']);
            } else {
                $descr = ucwords($entity_array[$index]['entPhysicalName']);
            }
            if ($descr) {
                $descr = rewrite_entity_descr($descr);
            } else {
                // Better sensor names for Arista EOS. Remove some redundancy and improve them so they reflect to which unit they belong.
                if ($device['os'] === 'arista_eos') {
                    $descr = $entity_array[$index]['entPhysicalDescr'];
                    if (preg_match('/(Input|Output) (voltage|current) sensor/i', $descr) || Str::startsWith($descr, 'Power supply') || preg_match('/^(Power Supply|Hotspot|Inlet|Board)/i', $descr)) {
                        $descr = ucwords($entity_array[substr_replace($index, '000', -3)]['entPhysicalDescr']) . ' ' . preg_replace('/(Voltage|Current|Power Supply) Sensor$/i', '', ucwords($entity_array[$index]['entPhysicalDescr']));
                    }
                    if (preg_match('/(temp|temperature) sensor$/i', $descr)) {
                        $descr = preg_replace('/(temp|temperature) sensor$/i', '', $descr);
                    }
                }
                // End better sensor names for Arista EOS.
                $descr = rewrite_entity_descr($descr);
            }
            $valid_sensor = check_entity_sensor($descr, $device);
            $type = $entitysensor[$entry['entPhySensorType']];
            // FIXME this stuff is foul
            if ($entry['entPhySensorScale'] == 'nano') {
                $divisor = '1000000000';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'micro') {
                $divisor = '1000000';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'milli') {
                $divisor = '1000';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'units') {
                $divisor = '1';
                $multiplier = '1';
            }
            if ($entry['entPhySensorScale'] == 'kilo') {
                $divisor = '1';
                $multiplier = '1000';
            }
            if ($entry['entPhySensorScale'] == 'mega') {
                $divisor = '1';
                $multiplier = '1000000';
            }
            if ($entry['entPhySensorScale'] == 'giga') {
                $divisor = '1';
                $multiplier = '1000000000';
            }
            if ($entry['entPhySensorScale'] == 'yocto') {
                $divisor = '1';
                $multiplier = '1';
            }
            if (is_numeric($entry['entPhySensorPrecision']) && $entry['entPhySensorPrecision'] > '0') {
                $divisor = $divisor . str_pad('', $entry['entPhySensorPrecision'], '0');
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
            if ($current == '-127' || ($device['os'] == 'asa' && Str::endsWith($device['hardware'], 'sc'))) {
                $valid_sensor = false;
            }
            // Check for valid sensors
            if ($entry['entPhySensorOperStatus'] === 'unavailable') {
                $valid_sensor = false;
            }
            if ($valid_sensor && dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE device_id = ? AND `sensor_class` = ? AND `sensor_type` = 'cisco-entity-sensor' AND `sensor_index` = ?", [$device['device_id'], $type, $index]) == '0') {
                // Check to make sure we've not already seen this sensor via cisco's entity sensor mib
                if ($type == 'power' && $device['os'] == 'arista_eos' && preg_match('/DOM (R|T)x Power/i', $descr)) {
                    $type = 'dbm';
                    $current = round(10 * log10($entry['entPhySensorValue'] / 10000), 3);
                    $multiplier = 1;
                    $divisor = 1;
                }

                if ($device['os'] === 'arista_eos') {
                    if ($entry['aristaEntSensorThresholdLowWarning'] != '-1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' && $entry['entPhySensorType'] == 'watts') {
                            $temp_low_warn_limit = $entry['aristaEntSensorThresholdLowWarning'] / 10000;
                            $low_warn_limit = round(10 * log10($temp_low_warn_limit), 2);
                        } else {
                            $low_warn_limit = $entry['aristaEntSensorThresholdLowWarning'] / $divisor;
                        }
                    }
                    if ($entry['aristaEntSensorThresholdLowCritical'] != '-1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' && $entry['entPhySensorType'] == 'watts') {
                            $temp_low_limit = $entry['aristaEntSensorThresholdLowCritical'] / 10000;
                            $low_limit = round(10 * log10($temp_low_limit), 2);
                        } else {
                            $low_limit = $entry['aristaEntSensorThresholdLowCritical'] / $divisor;
                        }
                    }
                    if ($entry['aristaEntSensorThresholdHighWarning'] != '1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' && $entry['entPhySensorType'] == 'watts') {
                            $temp_warn_limit = $entry['aristaEntSensorThresholdHighWarning'] / 10000;
                            $warn_limit = round(10 * log10($temp_warn_limit), 2);
                        } else {
                            $warn_limit = $entry['aristaEntSensorThresholdHighWarning'] / $divisor;
                        }
                    }
                    if ($entry['aristaEntSensorThresholdHighCritical'] != '1000000000') {
                        if ($entry['entPhySensorScale'] == 'milli' && $entry['entPhySensorType'] == 'watts') {
                            $temp_high_limit = $entry['aristaEntSensorThresholdHighCritical'] / 10000;
                            $high_limit = round(10 * log10($temp_high_limit), 2);
                        } else {
                            $high_limit = $entry['aristaEntSensorThresholdHighCritical'] / $divisor;
                        }
                    }
                    // Grouping sensors
                    $group = null;
                    if (preg_match('/DOM /i', $descr)) {
                        $group = 'SFPs';
                    } elseif (preg_match('/PwrCon/', $descr)) {
                        $string = explode(' ', $descr);
                        if (preg_match('/PwrCon[0-9]/', $string[0])) {
                            $group = $string[0];
                        } else {
                            $group = preg_replace('/PwrCon/i', '', $string[0]);
                        }
                        $descr = preg_replace('/^.*?(PwrCon)[0-9]*/i', '', $descr);
                    } elseif (preg_match('/^(Trident.*|Jericho[0-9]|FM6000)/i', $descr)) {
                        // I only know replies for Trident|Jericho|FM6000 platform. If you have another please add to the preg_match
                        $group = 'Platform';
                    } elseif (preg_match('/^(Power|PSU)/i', $descr)) {
                        $group = 'PSUs';
                    } else {
                        $group = 'System';
                        $descr = Str::replaceLast('temp sensor', '', $descr);
                    }
                    // End grouping sensors
                }
                discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'entity-sensor', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex, $entry['entSensorMeasuredEntity'], null, $group);
            }
        }//end if
    }//end foreach
    unset(
        $entity_array
    );
}//end if
echo "\n";
