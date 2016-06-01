<?php

// XUPS-MIB
if ($device['os'] == 'eatonups') {
    echo 'XUPS-MIB ';

    $ups_battery_level_oid = '.1.3.6.1.4.1.534.1.2.4.0';
    $ups_battery_level     = snmp_get($device, $ups_battery_level_oid, '-Oqv');

    if (is_numeric($ups_battery_level)) {
        discover_sensor($valid['sensor'], 'charge', $device, $ups_battery_level_oid, 'UPS Battery Level', $ups_device_manufacturer.' '.$ups_device_model, 'UPS Battery Level', '1', '1', null, null, null, null, $ups_battery_level);
    }

}//end if
