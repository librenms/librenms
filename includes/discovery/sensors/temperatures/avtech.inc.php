<?php

// AVTECH TEMPPAGER
if ($device['os'] == 'avtech') {
    echo 'AVTECH: ';
    if (strstr($device['sysObjectID'], '.20916.1.7')) {
    //  TemPageR 3E
        $divisor = 100;
        $internal_oid = '.1.3.6.1.4.1.20916.1.7.1.1.1.1.0'; //internal-tempc.0
        $internal_temp = snmp_get($device, $internal_oid, '-OvQ');
        $sen1_oid = '.1.3.6.1.4.1.20916.1.7.1.2.1.1.0'; //digital-sen1-1.0
        $sen1_temp = snmp_get($device, $sen1_oid, '-OvQ');
        $sen2_oid = '.1.3.6.1.4.1.20916.1.7.1.2.2.1.0'; //digital-sen2-1.0
        $sen2_temp = snmp_get($device, $sen2_oid, '-OvQ');

        if (!empty($internal_temp)) {
            $internal_desc = trim(snmp_get($device, '.1.3.6.1.4.1.20916.1.7.1.1.2.0', '-OvQ'), '"'); //internal.2.0
            discover_sensor($valid['sensor'], 'temperature', $device, $internal_oid, 0, $device['os'], $internal_desc, $divisor, '1', null, null, null, null, $internal_temp/$divisor);
        }
        if (!empty($sen1_temp)) {
            $sen1_desc = trim(snmp_get($device, '.1.3.6.1.4.1.20916.1.7.1.2.1.3.0', '-OvQ'), '"'); //digital-sen1-3.0
            discover_sensor($valid['sensor'], 'temperature', $device, $sen1_oid, 1, $device['os'], $sen1_desc, $divisor, '1', null, null, null, null, $sen1_temp/$divisor);
        }
        if (!empty($sen2_temp)) {
            $sen2_desc = trim(snmp_get($device, '.1.3.6.1.4.1.20916.1.7.1.2.2.3.0', '-OvQ'), '"'); //digital-sen2-3.0
            discover_sensor($valid['sensor'], 'temperature', $device, $sen2_oid, 2, $device['os'], $sen2_desc, $divisor, '1', null, null, null, null, $sen2_temp/$divisor);
        }
    }
    else if(strstr($device['sysObjectID'], '.20916.1.1')) {
    //  TemPageR 4E
        $divisor = 100;
        $internal_oid = '.1.3.6.1.4.1.20916.1.1.1.1.1.0'; //internal-tempc.0
        $internal_temp = snmp_get($device, $internal_oid, '-OvQ');
        $sen1_oid = '.1.3.6.1.4.1.20916.1.1.1.1.2.0'; //digital-sen1-1.0
        $sen1_temp = snmp_get($device, $sen1_oid, '-OvQ');
        $sen2_oid = '.1.3.6.1.4.1.20916.1.1.1.1.3.0'; //digital-sen2-1.0
        $sen2_temp = snmp_get($device, $sen2_oid, '-OvQ');
        $sen3_oid = '.1.3.6.1.4.1.20916.1.1.1.1.4.0'; //digital-sen3-1.0
        $sen3_temp = snmp_get($device, $sen3_oid, '-OvQ');

        if (!empty($internal_temp)) {
            $internal_desc = "Internal";
            $internal_max = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.1.0', '-OvQ') / $divisor;
            $internal_min = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.2.0', '-OvQ') / $divisor;
            discover_sensor($valid['sensor'], 'temperature', $device, $internal_oid, 0, $device['os'], $internal_desc, $divisor, '1', $internal_min, null, null, $internal_max,$internal_temp/$divisor);
        }
        if (!empty($sen1_temp)) {
            $sen1_desc = "Sensor 1";
            $sen1_max = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.3.0', '-OvQ') / $divisor;
            $sen1_min = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.4.0', '-OvQ') / $divisor;
            discover_sensor($valid['sensor'], 'temperature', $device, $sen1_oid, 1, $device['os'], $sen1_desc, $divisor, '1', $sen1_min, null, null, $sen1_max, $sen1_temp/$divisor);
        }
        if (!empty($sen2_temp)) {
            $sen2_desc = "Sensor 2";
            $sen2_max = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.5.0', '-OvQ') / $divisor;
            $sen2_min = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.6.0', '-OvQ') / $divisor;
            discover_sensor($valid['sensor'], 'temperature', $device, $sen2_oid, 2, $device['os'], $sen2_desc, $divisor, '1', $sen2_min, null, null, $sen2_max, $sen2_temp/$divisor);
        }
        if (!empty($sen3_temp)) {
            $sen3_desc = "Sensor 3";
            $sen3_max = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.7.0', '-OvQ') / $divisor;
            $sen3_min = snmp_get($device, '.1.3.6.1.4.1.20916.1.1.3.8.0', '-OvQ') / $divisor;
            discover_sensor($valid['sensor'], 'temperature', $device, $sen3_oid, 3, $device['os'], $sen3_desc, $divisor, '1', $sen3_min, null, null, $sen3_max, $sen3_temp/$divisor);
        }

    }
}//end if
