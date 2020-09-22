<?php

echo 'ProxySG ';
$fan_index = 0;
for ($index = 21; $index < 39; $index++) { //Proxy SG Fan OID end in 21-38
    $fanstatus_oid = ".1.3.6.1.4.1.3417.2.1.1.1.1.1.6.$index";
    $fanstatus = snmp_get($device, $fanstatus_oid, '-Oqv', 'BLUECOAT-SG-SENSOR-MIB');
    if ($fanstatus != 'notInstalled') {
        $fan_oid = ".1.3.6.1.4.1.3417.2.1.1.1.1.1.5.$index";
        $descr_oid = ".1.3.6.1.4.1.3417.2.1.1.1.1.1.9.$index";
        $limit_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.6.$index";
        $descr = snmp_get($device, $descr_oid, '-Oqv', 'BLUECOAT-SG-SENSOR-MIB');
        $current = snmp_get($device, $fan_oid, '-Oqv', 'BLUECOAT-SG-SENSOR-MIB');
        $divisor = '1';
        discover_sensor($valid['sensor'], 'fanspeed', $device, $fan_oid, $fan_index, 'sgos', $descr, 1, '1', null, null, null, null, $current);
    }
    $fan_index++;
}//end for
