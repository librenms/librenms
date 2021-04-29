<?php

$fabosSfpRxPower = snmpwalk_array_num($device, '.1.3.6.1.4.1.1588.2.1.1.1.28.1.1.4'); // FA-EXT-MIB::swSfpRxPower
$fabosSfpTxPower = snmpwalk_array_num($device, '.1.3.6.1.4.1.1588.2.1.1.1.28.1.1.5'); // FA-EXT-MIB::swSfpTxPower
if (! empty($fabosSfpRxPower) || ! empty($fabosSfpTxPower)) {
    $ifDescr = snmpwalk_group($device, 'ifDescr', 'IF-MIB', 0)['ifDescr'] ?? [];
}

foreach ($fabosSfpRxPower as $oid => $entry) {
    foreach ($entry as $index => $current) {
        if (is_numeric($current)) {
            $ifIndex = $index + 1073741823;
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                ".$oid.$index",
                'swSfpRxPower.' . $index,
                'brocade',
                makeshortif($ifDescr[$ifIndex]) . ' RX',
                1,
                1,
                -35,
                -30,
                -3,
                0,
                $current,
                'snmp',
                $ifIndex,
                'ports',
                null,
                'Receive Power'
            );
        }
    }
}

foreach ($fabosSfpTxPower as $oid => $entry) {
    foreach ($entry as $index => $current) {
        if (is_numeric($current)) {
            $ifIndex = $index + 1073741823;
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                ".$oid.$index",
                'swSfpTxPower.' . $index,
                'brocade',
                makeshortif($ifDescr[$ifIndex]) . ' TX',
                1,
                1,
                -5,
                null,
                null,
                0,
                $current,
                'snmp',
                $ifIndex,
                'ports',
                null,
                'Transmit Power');
        }
    }
}
