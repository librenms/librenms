<?php

echo ' OPENBSD-SENSORS-MIB: ';

echo 'Caching OIDs:';

$oids = [];
echo ' sensorDevice';
$oids = snmpwalk_cache_multi_oid($device, 'sensorDevice', $oids, 'OPENBSD-SENSORS-MIB');
echo ' sensorDescr';
$oids = snmpwalk_cache_multi_oid($device, 'sensorDescr', $oids, 'OPENBSD-SENSORS-MIB');
echo ' sensorValue';
$oids = snmpwalk_cache_multi_oid($device, 'sensorValue', $oids, 'OPENBSD-SENSORS-MIB');
echo ' sensorType';
$oids = snmpwalk_cache_multi_oid($device, 'sensorType', $oids, 'OPENBSD-SENSORS-MIB');

// temperature(0), fan(1), voltsdc(2), voltsac(3), resistance(4), power(5),
// current(6), watthour(7), amphour(8), indicator(9), raw(10), percent(11),
// illuminance(12), drive(13), timedelta(14), humidity(15), freq(16),
// angle(17), distance(18), pressure(19), accel(20)

$entitysensor['voltsdc'] = 'voltage';
$entitysensor['voltsac'] = 'voltage';
$entitysensor['fan'] = 'fanspeed';

$entitysensor['current'] = 'current';
$entitysensor['power'] = 'power';
$entitysensor['freq'] = 'freq';
$entitysensor['humidity'] = 'humidity';
$entitysensor['temperature'] = 'temperature';

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        // echo("[" . $entry['sensorType'] . "|" . $entry['sensorValue']. "|" . $index . "] ");
        if ($entitysensor[$entry['sensorType']] && is_numeric($entry['sensorValue']) && is_numeric($index)) {
            $entPhysicalIndex = $index;
            $oid = '.1.3.6.1.4.1.30155.2.1.2.1.5.' . $index;
            $current = $entry['sensorValue'];
            $descr = $entry['sensorDevice'] . ' ' . $entry['sensorDescr'];
            $bogus = false;

            $type = $entitysensor[$entry['sensorType']];

            if ($type == 'voltage') {
                $descr = preg_replace('/ voltage/i', '', $descr);
            }

            if ($type == 'temperature') {
                if ($current < -40 || $current > 200) {
                    $bogus = true;
                }
                $descr = preg_replace('/ temperature/i', '', $descr);
            }

            // echo($descr . "|" . $index . "|" .$current . "|" . $bogus . "\n");
            if (! $bogus) {
                discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'openbsd-sensor', $descr, '1', '1', null, null, null, null, $current);
            }
        }//end if
    }//end foreach
}//end if

echo "\n";
