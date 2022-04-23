<?php

echo 'ProxySG ';
$temp_index = 0;
for ($index = 1; $index < 20; $index++) { //Proxy SG Temp OID end in 1-20
    $tempstat_oid = ".1.3.6.1.4.1.3417.2.1.1.1.1.1.6.$index";
    $temp = snmp_get($device, $tempstat_oid, '-Oqv', 'BLUECOAT-SG-SENSOR-MIB');
    if ($temp != 'notInstalled') {
        $temp_oid = ".1.3.6.1.4.1.3417.2.1.1.1.1.1.5.$index";
        $descr_oid = ".1.3.6.1.4.1.3417.2.1.1.1.1.1.9.$index";
        $descr = snmp_get($device, $descr_oid, '-Oqv', 'BLUECOAT-SG-SENSOR-MIB');
        $current = snmp_get($device, $temp_oid, '-Oqv', 'BLUECOAT-SG-SENSOR-MIB');
        $divisor = '1';
        discover_sensor($valid['sensor'], 'temperature', $device, $temp_oid, $temp_index, 'sgos', $descr, 1, '1', null, null, null, null, $current);
    }
    $temp_index++;
}
