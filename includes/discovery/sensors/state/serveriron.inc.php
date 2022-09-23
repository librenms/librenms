<?php

echo 'ServerIron States';

// All those states are : (Value: 1 Other, 2 Normal, 3 failure) (Power Supply and Fans)

// Power Supplies
for ($i = 1; $i != 3; $i++) {
    $power_oid = '.1.3.6.1.4.1.1991.1.1.1.2.1.1.3.' . $i;
    $power_status = snmp_get($device, $power_oid, '-Oqv');
    if (! empty($power_status)) {
        discover_sensor($valid['sensor'], 'state', $device, $power_oid, 'powerstatus' . $i, 'snmp', 'Power Supply ' . $i . ' Status', 1, 1, '1', null, null, '3', $power_status);
    }
}

// Fan status
for ($i = 1; $i != 7; $i++) {
    $fan_oid = '.1.3.6.1.4.1.1991.1.1.1.3.1.1.3.' . $i;
    $fan_status = snmp_get($device, $fan_oid, '-Oqv');
    if (! empty($fan_status)) {
        discover_sensor($valid['sensor'], 'state', $device, $fan_oid, 'fanstatus' . $i, 'snmp', 'Fan ' . $i . ' Status', 1, 1, '1', null, null, '3', $fan_status);
    }
}
