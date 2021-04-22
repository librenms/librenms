<?php

/*** VOIP ***/
$carriersTable = snmpwalk_cache_multi_oid($device, 'fbSipCarrierTable', [], 'FIREBRICK-VOIP-MIB', 'firebrick');

if ($carriersTable) {
    // It has a carriers table

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.4.1.24693.100.5060.1.0',
        '1',
        'firebrick',
        'Total active call legs',
        '1',
        '1',
        0,
        0,
        800,
        1000,
        );

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.4.1.24693.100.5060.2.0',
        '2',
        'firebrick',
        'RADIUS-based registrations',
        '1',
        '1',
        0,
        0,
        800,
        1000,
        );

    foreach ($carriersTable as $idx => $carrier) {
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            '.1.3.6.1.4.1.24693.100.5060.3.1.2.' . $idx,
            'carrier' . $idx,
            'firebrick',
            'Total Legs: ' . $carrier['fbSipCarrierName'],
            '1',
            '1',
            0,
            0,
            800,
            1000,
            );
        discover_sensor(
            $valid['sensor'],
            'count',
            $device,
            '.1.3.6.1.4.1.24693.100.5060.3.1.3.' . $idx,
            'carrier' . $idx,
            'firebrick',
            'Connected Legs: ' . $carrier['fbSipCarrierName'],
            '1',
            '1',
            0,
            0,
            800,
            1000,
            );
    }
}
