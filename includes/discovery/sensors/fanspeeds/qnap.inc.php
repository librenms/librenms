<?php

// Turbo NAS Fanspeed
$fan_speed_oid = '.1.3.6.1.4.1.24681.1.2.15.1.3.';

if ($device['os'] == 'qnap') {
    $oids = snmpwalk_cache_multi_oid($device, 'SystemFanTable', array(), 'NAS-MIB');

    // Parse all fans in the device to get the speed
    if (is_array($oids)) {
        foreach ($oids as $fan_number => $entry) {
            // Get the fan speed full oid
            $fan_oid = $fan_speed_oid.$fan_number;
            // Get the fan speed
            $fan_speed = $entry['SysFanSpeed'];
            // Getting the fan information
            $fan_information = $entry['SysFanDescr'];
            // Save the temperature for the disk
            discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_oid, $fan_number, 'snmp', $fan_information, '1', '1', null, null, null, null, $fan_speed);
        }
    }
}//end if
