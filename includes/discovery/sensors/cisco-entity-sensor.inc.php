<?php

use Illuminate\Support\Str;
use LibreNMS\Util\ProgressList;

if ($device['os_group'] == 'cisco') {
    $oids = [];
    $progress = ProgressList::start('CISCO-ENTITY-SENSOR caching OIDs');

    if (empty($entity_array)) {
        $tmp_oids = ['entPhysicalDescr', 'entPhysicalName', 'entPhysicalClass', 'entPhysicalContainedIn', 'entPhysicalParentRelPos'];
        $entity_array = [];
        foreach ($tmp_oids as $tmp_oid) {
            $progress->add($tmp_oid);
            SnmpQuery::enumStrings()->walk("ENTITY-MIB::$tmp_oid")->table(1, $entity_array);
        }
        $progress->add('entAliasMappingIdentifier');
        SnmpQuery::enumStrings()->walk('ENTITY-MIB::entAliasMappingIdentifier')->table(2, $entity_array);
    }

    $port_array = [];
    $port_array = SnmpQuery::enumStrings()->walk('IF-MIB::ifName')->table(1);
    $port_reverse_array = [];
    foreach ($port_array as $index => $port) {
        if (! isset($port['IF-MIB::ifName'])) {
            continue;
        }
        $port['ifIndex'] = $index;
        $port_reverse_array[$port['IF-MIB::ifName']] = $port;
    }
    d_echo($port_reverse_array);

    foreach (['entSensorType', 'entSensorScale', 'entSensorValue', 'entSensorMeasuredEntity', 'entSensorPrecision'] as $tmp_oid) {
        $progress->add($tmp_oid);
        SnmpQuery::enumStrings()->walk("CISCO-ENTITY-SENSOR-MIB::$tmp_oid")->table(1, $oids);
    }

    $t_oids = [];
    $progress->add('entSensorThresholdSeverity');
    SnmpQuery::enumStrings()->walk('CISCO-ENTITY-SENSOR-MIB::entSensorThresholdSeverity')->table(2, $t_oids);
    $progress->add('entSensorThresholdRelation');
    SnmpQuery::enumStrings()->walk('CISCO-ENTITY-SENSOR-MIB::entSensorThresholdRelation')->table(2, $t_oids);
    $progress->add('entSensorThresholdValue');
    SnmpQuery::enumStrings()->walk('CISCO-ENTITY-SENSOR-MIB::entSensorThresholdValue')->table(2, $t_oids);
    $progress->end();

    d_echo($oids);

    $entitysensor['voltsDC'] = 'voltage';
    $entitysensor['voltsAC'] = 'voltage';
    $entitysensor['amperes'] = 'current';
    $entitysensor['watt'] = 'power';
    $entitysensor['hertz'] = 'freq';
    $entitysensor['percentRH'] = 'humidity';
    $entitysensor['rpm'] = 'fanspeed';
    $entitysensor['celsius'] = 'temperature';
    $entitysensor['watts'] = 'power';
    $entitysensor['dBm'] = 'dbm';

    if (is_array($oids)) {
        foreach ($oids as $index => $entry) {
            $sensor_type = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorType'] ?? null;
            $sensor_value = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorValue'] ?? null;
            $sensor_scale = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorScale'] ?? null;
            $sensor_measured = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorMeasuredEntity'] ?? null;
            $sensor_precision = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorPrecision'] ?? null;

            // echo("[" . $sensor_type . "|" . $sensor_value . "|" . $index . "]");
            if (isset($sensor_type, $sensor_value, $entitysensor[$sensor_type]) && $entitysensor[$sensor_type] && is_numeric($sensor_value) && is_numeric($index)) {
                $group = null;
                $entPhysicalIndex = $index;
                if ($entity_array[$index]['ENTITY-MIB::entPhysicalName'] || $device['os'] == 'iosxr') {
                    $descr = rewrite_entity_descr($entity_array[$index]['ENTITY-MIB::entPhysicalName']);
                } else {
                    $descr = rewrite_entity_descr($entity_array[$index]['ENTITY-MIB::entPhysicalDescr']);
                }

                // Set description based on measured entity if it exists
                if (isset($sensor_measured) && is_numeric($sensor_measured) && $sensor_measured) {
                    $measured_descr = $entity_array[$sensor_measured]['ENTITY-MIB::entPhysicalName'];
                    if (! $measured_descr) {
                        $measured_descr = $entity_array[$sensor_measured]['ENTITY-MIB::entPhysicalDescr'];
                    }

                    $descr = $measured_descr . ' - ' . $descr;
                }

                // Bit dirty also, clean later
                $descr = str_replace('Temp: ', '', $descr);
                $descr = str_ireplace(' temperature', '', $descr);
                $descr = trim($descr);

                $oid = '.1.3.6.1.4.1.9.9.91.1.1.1.1.4.' . $index;
                $current = $sensor_value;
                $type = $entitysensor[$sensor_type];

                // Try to handle the scale
                [$divisor, $multiplier] = match ($sensor_scale) {
                    'zepto' => [1000000000000000000, 1],
                    'nano' => [1000000000, 1],
                    'micro' => [1000000, 1],
                    'milli' => [1000, 1],
                    'units' => [1, 1],
                    'kilo' => [1, 1000],
                    'mega' => [1, 1000000],
                    'giga' => [1, 1000000000],
                    'yocto' => [1, 1],
                    default => [1, 1],
                };

                if (is_numeric($sensor_precision)
                        && $sensor_precision > '0'
                        // Workaround for a Cisco SNMP bug
                        && $sensor_precision != '1615384784'
                ) {
                    // Use precision value to determine decimal point place on returned value, then apply divisor
                    $divisor = (10 ** $sensor_precision) * $divisor;
                }

                $current = ($current * $multiplier / $divisor);

                // Set thresholds to null
                $limit = null;
                $limit_low = null;
                $warn_limit = null;
                $warn_limit_low = null;
                $other_limit = null;
                $other_limit_low = null;

                // Check thresholds for this entry (bit dirty, but it works!)
                if (isset($t_oids[$index]) && is_array($t_oids[$index])) {
                    foreach ($t_oids[$index] as $key) {
                        $threshold_severity = $key['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdSeverity'] ?? null;
                        $threshold_relation = $key['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdRelation'] ?? null;
                        $threshold_value = $key['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdValue'] ?? null;

                        // Skip invalid treshold values
                        if ($threshold_value === null || $threshold_value == '-32768' || $threshold_value == '2147483647') {
                            continue;
                        } elseif ($type == 'fanspeed' && $threshold_value == '-1') {
                            continue;
                        }
                        // Critical Limit
                        if (($threshold_severity == 'major' || $threshold_severity == 'critical') && ($threshold_relation == 'greaterOrEqual' || $threshold_relation == 'greaterThan')) {
                            if ($threshold_value == '0' && isset($limit)) {
                                // Ignore a threshold of 0 if another threshold has been set (major vs critical)
                                continue;
                            }
                            $limit = ($threshold_value * $multiplier / $divisor);
                        }

                        if (($threshold_severity == 'major' || $threshold_severity == 'critical') && ($threshold_relation == 'lessOrEqual' || $threshold_relation == 'lessThan')) {
                            if ($threshold_value == '0' && isset($limit_low)) {
                                // Ignore a threshold of 0 if another threshold has been set (major vs critical)
                                continue;
                            }
                            $limit_low = ($threshold_value * $multiplier / $divisor);
                        }

                        // Warning Limit
                        if ($threshold_severity == 'minor' && ($threshold_relation == 'greaterOrEqual' || $threshold_relation == 'greaterThan')) {
                            $warn_limit = ($threshold_value * $multiplier / $divisor);
                        }

                        if ($threshold_severity == 'minor' && ($threshold_relation == 'lessOrEqual' || $threshold_relation == 'lessThan')) {
                            $warn_limit_low = ($threshold_value * $multiplier / $divisor);
                        }

                        // Other Limit
                        if ($threshold_severity == 'other' && ($threshold_relation == 'greaterOrEqual' || $threshold_relation == 'greaterThan')) {
                            $other_limit = ($threshold_value * $multiplier / $divisor);
                        }

                        if ($threshold_severity == 'other' && ($threshold_relation == 'lessOrEqual' || $threshold_relation == 'lessThan')) {
                            $other_limit_low = ($threshold_value * $multiplier / $divisor);
                        }
                    }//end foreach
                }//end if

                // If temperature sensor, set low thresholds to -1 and -5. Many sensors don't return low thresholds, therefore LibreNMS takes the runtime low
                // Also changing 0 values (not just null) as Libre loses these somewhere along the line and shows an empty value in the Web UI
                if ($type == 'temperature') {
                    if ($warn_limit_low == 0) {
                        $warn_limit_low = -1;
                    }
                    if ($limit_low == 0) {
                        $limit_low = -5;
                    }
                }

                // Handle platforms that do not have the "Minor"/"Major"/"Critical" threshold for a sensor but instead only have the "Other" threshold for such sensor (ISR1k / ISR4k)
                // Since there is no other threshold available other than "Other", treat these as Critical Limit
                if (! isset($limit_low) && ! isset($warn_limit_low) && isset($other_limit_low)) {
                    $limit_low = $other_limit_low;
                }
                if (! isset($limit) && ! isset($warn_limit) && isset($other_limit)) {
                    $limit = $other_limit;
                }

                // End Threshold code
                $ok = true;

                if ($current == '-127' || $descr == '') {
                    $ok = false;
                }

                if ($ok) {
                    $phys_index = $entity_array[$index]['ENTITY-MIB::entPhysicalContainedIn'];
                    $tmp_ifindex = 0;
                    while ($phys_index != 0) {
                        if ($index === $phys_index) {
                            break;
                        }

                        if (! isset($entity_array[$phys_index])) {
                            // NX-OS: entPhysicalContainedIn may reference a parent index (e.g. 1)
                            // that is not returned by the SNMP walk — stop traversal safely.
                            break;
                        }

                        $entPhysicalClass = $entity_array[$phys_index]['ENTITY-MIB::entPhysicalClass'];
                        $entPhysicalName = $entity_array[$phys_index]['ENTITY-MIB::entPhysicalName'];
                        $transceivers = \App\Models\Transceiver::where('device_id', $device['device_id'])->where('index', '=', $phys_index)->first();
                        if (! empty($transceivers)) {
                            // If we already have a mapping done in transceivers, let's use it.
                            $entPhysicalIndex = $phys_index;
                            $sensor_measured = 'ports';
                            $group = 'transceiver';
                            break;
                        }
                        //either sensor is contained by a port class entity.
                        if ($entPhysicalClass === 'port') {
                            $entAliasMappingIdentifier = $entity_array[$phys_index][0]['ENTITY-MIB::entAliasMappingIdentifier'];
                            if (Str::contains($entAliasMappingIdentifier, 'ifIndex.')) {
                                [, $tmp_ifindex] = explode('.', (string) $entAliasMappingIdentifier);
                            }
                            break;
                            //or sensor entity has a parent entity with module class and entPhysicalName set to an existing ifName.
                        } elseif ($entPhysicalClass === 'module' && array_key_exists($entPhysicalName, $port_reverse_array)) {
                            $tmp_ifindex = $port_reverse_array[$entPhysicalName]['ifIndex'];
                            break;
                        } else {
                            $phys_index = $entity_array[$phys_index]['ENTITY-MIB::entPhysicalContainedIn'];
                        }
                    }
                    if ($tmp_ifindex != 0) {
                        $port_id = PortCache::getIdFromIfIndex($tmp_ifindex, $device['device_id']);
                        if ($port_id) {
                            $entPhysicalIndex = $phys_index;
                            $sensor_measured = 'ports';
                            $group = 'transceiver';
                        }
                    }

                    discover_sensor(null, $type, $device, $oid, $index, 'cisco-entity-sensor', ucwords($descr), $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $sensor_measured ?? null, null, $group);
                    //Cisco IOS-XR : add a fake sensor to graph as dbm
                    if ($type == 'power' and $device['os'] == 'iosxr' and (preg_match('/power (R|T)x/i', $descr) or preg_match('/(R|T)x Power/i', $descr) or preg_match('/(R|T)x Lane/i', $descr))) {
                        // convert Watts to dbm
                        $user_func = 'mw_to_dbm';
                        $type = 'dbm';
                        $multiplier = 1000;
                        $limit_low = isset($limit_low) ? round(mw_to_dbm($limit_low * $multiplier), 3) : null;
                        $warn_limit_low = isset($limit_low) ? round(mw_to_dbm($warn_limit_low * $multiplier), 3) : null;
                        $warn_limit = isset($limit_low) ? round(mw_to_dbm($warn_limit * $multiplier), 3) : null;
                        $limit = isset($limit_low) ? round(mw_to_dbm($limit * $multiplier), 3) : null;
                        $current = mw_to_dbm($current * $multiplier);
                        //echo("\n".$valid['sensor'].", $type, $device, $oid, $index, 'cisco-entity-sensor', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, $user_func");
                        discover_sensor(null, $type, $device, $oid, $index, 'cisco-entity-sensor', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $sensor_measured ?? null, $user_func, $group);
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

    $progress = ProgressList::start('CISCO-ENTITY-SENSOR sensors');
    foreach (array_flip($entitysensor) as $type) {
        app('sensor-discovery')->sync(sensor_class: $type, poller_type: 'snmp');
    }
    $progress->end();
}//end if
