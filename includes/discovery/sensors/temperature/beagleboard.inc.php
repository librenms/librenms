<?php
/*
 * Temperature Sensors for BeagleBoard
 * Requires snmp extend agent script from librenms-agent
 */

$type = 'beagleboardTemp';

// $oid = 'NET-SNMP-EXTEND-MIB::nsExtendOutLine."beagleboard"';
// Discovery is fine, but Polling seems to fail with string OID. So snmptranslate,
//    snmptranslate -On NET-SNMP-EXTEND-MIB::nsExtendOutLine.\"beagleboard\"
// to,
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.11.98.101.97.103.108.101.98.111.97.114.100';
$value = explode("\n", trim(snmp_walk($device, $oid, '-Oqve'), '"'));
for ($temp = 0; $temp < 5; $temp++) {
    switch ($temp) {
        case '0':
            $descr = 'CPU';
            break;
        case '1':
            $descr = 'GPU';
            break;
        case '2':
            $descr = 'Core';
            break;
        case '3':
            $descr = 'DSP';
            break;
        case '4':
            $descr = 'IVA-HD';
            break;
    }
    if (is_numeric($value[$temp])) {
        // Need to scale down by 1000 (initial value, and added sensor). Scaling values are integer, but accepted approach seems to be setting as a string
        discover_sensor($valid['sensor'], 'temperature', $device, $oid . '.' . ($temp + 1), $temp, $type, $descr, '1000', '1', null, null, null, null, $value[$temp] / 1000);
    } else {
        break;
    }
}
