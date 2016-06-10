<?php

// MGE UPS Voltages
if ($device['os'] == 'mgeups') {
    echo 'MGE ';

    // Battery Charge Percent
    $ups_battery_level_oid = '.1.3.6.1.4.1.705.1.5.2.0';
    $ups_battery_level     = snmp_get($device, $ups_battery_level_oid, '-Oqv');

    if (is_numeric($ups_battery_level)) {
        discover_sensor($valid['sensor'], 'charge', $device, $ups_battery_level_oid, 'UPS Battery Level', $ups_device_manufacturer.' '.$ups_device_model, 'Battery Charge', '1', '1', null, null, null, null, $ups_battery_level);
    }

}//end if
